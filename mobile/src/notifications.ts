import { LocalNotifications } from '@capacitor/local-notifications'
import { Preferences } from '@capacitor/preferences'
import { api, type Shift } from './api'

const SETTINGS_KEY = 'hrms.reminders'

/** How far ahead we lay down reminders; refreshed every time the app opens. */
const HORIZON_DAYS = 14

export type ReminderSettings = {
  checkInEnabled: boolean
  /** Minutes before the shift starts. */
  checkInOffset: number
  checkOutEnabled: boolean
  /** Minutes relative to the shift end; negative is before, positive is after. */
  checkOutOffset: number
}

export const DEFAULT_SETTINGS: ReminderSettings = {
  checkInEnabled: true,
  checkInOffset: 5,
  checkOutEnabled: true,
  checkOutOffset: 0,
}

export const CHECK_IN_CHOICES = [
  { value: 15, label: '15 min before' },
  { value: 10, label: '10 min before' },
  { value: 5, label: '5 min before' },
  { value: 0, label: 'At shift start' },
]

export const CHECK_OUT_CHOICES = [
  { value: -10, label: '10 min before' },
  { value: -5, label: '5 min before' },
  { value: 0, label: 'At shift end' },
  { value: 5, label: '5 min after' },
  { value: 15, label: '15 min after' },
]

export async function loadSettings(): Promise<ReminderSettings> {
  const { value } = await Preferences.get({ key: SETTINGS_KEY })
  if (!value) return DEFAULT_SETTINGS

  try {
    return { ...DEFAULT_SETTINGS, ...(JSON.parse(value) as Partial<ReminderSettings>) }
  } catch {
    return DEFAULT_SETTINGS
  }
}

export async function saveSettings(settings: ReminderSettings): Promise<void> {
  await Preferences.set({ key: SETTINGS_KEY, value: JSON.stringify(settings) })
}

export async function ensurePermission(): Promise<boolean> {
  try {
    const current = await LocalNotifications.checkPermissions()
    if (current.display === 'granted') return true

    const asked = await LocalNotifications.requestPermissions()
    return asked.display === 'granted'
  } catch {
    return false
  }
}

/**
 * Stable per-day ids so a single day's reminder can be cancelled on its own
 * — which is what stops the app nagging someone who has already punched.
 */
function notificationId(date: Date, kind: 'in' | 'out'): number {
  const month = date.getMonth() + 1
  const day = date.getDate()
  return month * 1000 + day * 10 + (kind === 'in' ? 1 : 2)
}

function atTime(day: Date, hhmm: string, offsetMinutes: number): Date {
  const [hours, minutes] = hhmm.split(':').map(Number)
  const when = new Date(day)
  when.setHours(hours, minutes + offsetMinutes, 0, 0)
  return when
}

/**
 * Wipes the schedule and lays down a fresh one. Called on launch, after a
 * punch and whenever the settings change, so the reminders always reflect
 * the current shift, the current settings and what has already been done
 * today.
 */
export async function rescheduleReminders(options: {
  shift: Shift | null
  settings: ReminderSettings
  holidays: string[]
  checkedInToday: boolean
  checkedOutToday: boolean
}): Promise<void> {
  const { shift, settings, holidays, checkedInToday, checkedOutToday } = options

  try {
    const pending = await LocalNotifications.getPending()
    if (pending.notifications.length) {
      await LocalNotifications.cancel({ notifications: pending.notifications })
    }
  } catch {
    // Nothing scheduled yet, or the platform has no notification support.
  }

  if (!shift) return
  if (!settings.checkInEnabled && !settings.checkOutEnabled) return

  const granted = await ensurePermission()
  if (!granted) return

  const now = new Date()
  const offDays = shift.off_days ?? []
  const toSchedule: Parameters<typeof LocalNotifications.schedule>[0]['notifications'] = []

  for (let i = 0; i < HORIZON_DAYS; i++) {
    const day = new Date(now)
    day.setDate(now.getDate() + i)
    day.setHours(0, 0, 0, 0)

    const dayName = day.toLocaleDateString('en-US', { weekday: 'long' })
    const iso = `${day.getFullYear()}-${String(day.getMonth() + 1).padStart(2, '0')}-${String(day.getDate()).padStart(2, '0')}`

    if (offDays.includes(dayName) || holidays.includes(iso)) continue

    const isToday = i === 0

    if (settings.checkInEnabled && !(isToday && checkedInToday)) {
      const when = atTime(day, shift.start_time, -settings.checkInOffset)

      if (when > now) {
        toSchedule.push({
          id: notificationId(day, 'in'),
          title: 'Time to check in',
          body:
            settings.checkInOffset > 0
              ? `Your shift starts in ${settings.checkInOffset} minutes. If you are at the office, mark your attendance.`
              : 'Your shift starts now. If you are at the office, mark your attendance.',
          schedule: { at: when, allowWhileIdle: true },
          smallIcon: 'ic_stat_hrms',
        })
      }
    }

    if (settings.checkOutEnabled && !(isToday && checkedOutToday)) {
      const when = atTime(day, shift.end_time, settings.checkOutOffset)

      // An overnight shift ends on the next calendar day.
      if (shift.end_time <= shift.start_time) {
        when.setDate(when.getDate() + 1)
      }

      if (when > now) {
        toSchedule.push({
          id: notificationId(day, 'out'),
          title: 'Time to check out',
          body: 'Your shift is wrapping up. Remember to check out before you leave.',
          schedule: { at: when, allowWhileIdle: true },
          smallIcon: 'ic_stat_hrms',
        })
      }
    }
  }

  if (!toSchedule.length) return

  try {
    await LocalNotifications.schedule({ notifications: toSchedule })
  } catch {
    // A refused or unsupported schedule must never break the screen that
    // asked for it.
  }
}

/**
 * Convenience wrapper: pulls the settings and this year's holidays, then
 * lays the schedule down. Safe to call on every home-screen refresh.
 */
export async function syncReminders(
  shift: Shift | null,
  state: { checkedInToday: boolean; checkedOutToday: boolean },
): Promise<void> {
  const settings = await loadSettings()

  let holidays: string[] = []

  try {
    const result = await api.holidays(new Date().getFullYear())
    holidays = result.data.filter((h) => !h.is_past).map((h) => h.date)
  } catch {
    // Reminders are still useful without the holiday list.
  }

  await rescheduleReminders({ shift, settings, holidays, ...state })
}

/** Drops just today's reminder once the matching punch has happened. */
export async function cancelTodayReminder(kind: 'in' | 'out'): Promise<void> {
  const now = new Date()
  const ids = [notificationId(now, kind)]

  // A night shift's check-out reminder is filed under the day the shift
  // started, so a punch made after midnight has to clear that one too.
  if (kind === 'out') {
    const yesterday = new Date(now)
    yesterday.setDate(now.getDate() - 1)
    ids.push(notificationId(yesterday, 'out'))
  }

  try {
    await LocalNotifications.cancel({ notifications: ids.map((id) => ({ id })) })
  } catch {
    // Already gone, which is the outcome we wanted anyway.
  }
}
