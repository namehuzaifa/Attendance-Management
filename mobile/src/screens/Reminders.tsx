import { useEffect, useState } from 'react'
import { LocalNotifications } from '@capacitor/local-notifications'
import {
  CHECK_IN_CHOICES,
  CHECK_OUT_CHOICES,
  type ReminderSettings,
  ensurePermission,
  loadSettings,
  saveSettings,
} from '../notifications'
import type { Shift } from '../api'
import { Alert, Sheet } from '../ui'

/**
 * Per-device reminder preferences. Either alert can be switched off on its
 * own, or both, and the timing of each is the employee's choice.
 */
export default function Reminders({
  shift,
  onClose,
  onSaved,
  onToast,
}: {
  shift: Shift | null
  onClose: () => void
  onSaved: (settings: ReminderSettings) => Promise<void>
  onToast: (message: string, tone?: 'success' | 'error') => void
}) {
  const [settings, setSettings] = useState<ReminderSettings | null>(null)
  const [busy, setBusy] = useState(false)
  const [blocked, setBlocked] = useState(false)

  useEffect(() => {
    loadSettings().then(setSettings)

    LocalNotifications.checkPermissions()
      .then((status) => setBlocked(status.display === 'denied'))
      .catch(() => setBlocked(false))
  }, [])

  if (!settings) return null

  function patch(change: Partial<ReminderSettings>) {
    setSettings((current) => (current ? { ...current, ...change } : current))
  }

  async function save() {
    if (!settings) return
    setBusy(true)

    try {
      // Only ask for permission when something is actually switched on.
      if (settings.checkInEnabled || settings.checkOutEnabled) {
        const granted = await ensurePermission()

        if (!granted) {
          setBlocked(true)
          onToast('Allow notifications in Android settings to get reminders.', 'error')
          setBusy(false)
          return
        }
      }

      await saveSettings(settings)
      await onSaved(settings)
      onToast('Reminder settings saved.', 'success')
      onClose()
    } catch {
      onToast('Could not save your reminder settings.', 'error')
    } finally {
      setBusy(false)
    }
  }

  const bothOff = !settings.checkInEnabled && !settings.checkOutEnabled

  return (
    <Sheet title="Attendance reminders" onClose={onClose}>
      {!shift && (
        <Alert tone="info">
          You have no shift assigned yet, so there are no times to remind you about.
        </Alert>
      )}

      {blocked && (
        <Alert tone="error">
          Notifications are blocked for this app. Turn them on in Android settings first.
        </Alert>
      )}

      {/* ---------------------------------------------------- check in */}
      <div className="card" style={{ marginBottom: 14 }}>
        <div className="row" style={{ paddingTop: 0 }}>
          <div className="row__main">
            <div className="row__title">Check-in reminder</div>
            <div className="row__sub">
              {shift ? `Shift starts at ${shift.start_time}` : 'No shift set'}
            </div>
          </div>
          <Switch
            checked={settings.checkInEnabled}
            onChange={(value) => patch({ checkInEnabled: value })}
            label="Check-in reminder"
          />
        </div>

        {settings.checkInEnabled && (
          <div className="field" style={{ marginTop: 12, marginBottom: 0 }}>
            <label htmlFor="checkInOffset">Remind me</label>
            <select
              id="checkInOffset"
              value={settings.checkInOffset}
              onChange={(e) => patch({ checkInOffset: Number(e.target.value) })}
            >
              {CHECK_IN_CHOICES.map((choice) => (
                <option key={choice.value} value={choice.value}>{choice.label}</option>
              ))}
            </select>
          </div>
        )}
      </div>

      {/* --------------------------------------------------- check out */}
      <div className="card" style={{ marginBottom: 14 }}>
        <div className="row" style={{ paddingTop: 0 }}>
          <div className="row__main">
            <div className="row__title">Check-out reminder</div>
            <div className="row__sub">
              {shift ? `Shift ends at ${shift.end_time}` : 'No shift set'}
            </div>
          </div>
          <Switch
            checked={settings.checkOutEnabled}
            onChange={(value) => patch({ checkOutEnabled: value })}
            label="Check-out reminder"
          />
        </div>

        {settings.checkOutEnabled && (
          <div className="field" style={{ marginTop: 12, marginBottom: 0 }}>
            <label htmlFor="checkOutOffset">Remind me</label>
            <select
              id="checkOutOffset"
              value={settings.checkOutOffset}
              onChange={(e) => patch({ checkOutOffset: Number(e.target.value) })}
            >
              {CHECK_OUT_CHOICES.map((choice) => (
                <option key={choice.value} value={choice.value}>{choice.label}</option>
              ))}
            </select>
          </div>
        )}
      </div>

      <p className="field__hint" style={{ marginBottom: 16 }}>
        {bothOff
          ? 'Both reminders are off — the app will stay quiet.'
          : 'One reminder per day, and it is dropped as soon as you punch. Weekly offs and public holidays are skipped.'}
      </p>

      <button className="btn btn--primary" onClick={save} disabled={busy}>
        {busy && <span className="spinner" />}
        Save settings
      </button>
    </Sheet>
  )
}

function Switch({
  checked,
  onChange,
  label,
}: {
  checked: boolean
  onChange: (value: boolean) => void
  label: string
}) {
  return (
    <button
      type="button"
      role="switch"
      aria-checked={checked}
      aria-label={label}
      onClick={() => onChange(!checked)}
      style={{
        width: 50,
        height: 29,
        borderRadius: 999,
        padding: 3,
        flex: '0 0 50px',
        display: 'flex',
        justifyContent: checked ? 'flex-end' : 'flex-start',
        background: checked ? 'linear-gradient(135deg, var(--violet-deep), var(--violet))' : 'rgba(255,255,255,0.14)',
        transition: 'background 0.2s ease',
      }}
    >
      <span
        style={{
          width: 23,
          height: 23,
          borderRadius: '50%',
          background: '#fff',
          boxShadow: '0 2px 6px rgba(0,0,0,0.4)',
          transition: 'transform 0.2s ease',
        }}
      />
    </button>
  )
}
