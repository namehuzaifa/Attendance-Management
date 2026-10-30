import { useCallback, useEffect, useRef, useState } from 'react'
import { Geolocation } from '@capacitor/geolocation'
import { Haptics, ImpactStyle } from '@capacitor/haptics'
import { api, type HomePayload, type User } from '../api'
import { cancelTodayReminder, syncReminders } from '../notifications'
import { Alert, Empty, Gauge, Loader, humanMinutes } from '../ui'

type Props = {
  user: User
  onToast: (message: string, tone?: 'success' | 'error') => void
  onExpired: () => void
}

/**
 * Location is a nice-to-have on a punch: if the device refuses or takes too
 * long, the punch still goes through rather than stranding the employee.
 */
async function tryCoords(): Promise<{ latitude: number; longitude: number } | undefined> {
  try {
    const permission = await Geolocation.checkPermissions()

    if (permission.location !== 'granted') {
      const asked = await Geolocation.requestPermissions()
      if (asked.location !== 'granted') return undefined
    }

    const position = await Geolocation.getCurrentPosition({
      enableHighAccuracy: false,
      timeout: 7000,
      maximumAge: 60000,
    })

    return {
      latitude: position.coords.latitude,
      longitude: position.coords.longitude,
    }
  } catch {
    return undefined
  }
}

export default function Home({ user, onToast, onExpired }: Props) {
  const [data, setData] = useState<HomePayload | null>(null)
  const [loading, setLoading] = useState(true)
  const [punching, setPunching] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [now, setNow] = useState(() => new Date())
  const mounted = useRef(true)

  const load = useCallback(async () => {
    try {
      const result = await api.home()
      if (mounted.current) {
        setData(result.data)
        setError(null)
      }

      // Keep the reminder schedule in step with the shift and with what has
      // already been punched today.
      void syncReminders(user.shift, {
        checkedInToday: Boolean(result.data.today?.check_in),
        checkedOutToday: Boolean(result.data.today?.check_out),
      })
    } catch (err: any) {
      if (err?.status === 401) {
        onExpired()
        return
      }
      if (mounted.current) setError(err?.message ?? 'Could not load your day.')
    } finally {
      if (mounted.current) setLoading(false)
    }
  }, [onExpired, user.shift])

  useEffect(() => {
    mounted.current = true
    load()
    return () => {
      mounted.current = false
    }
  }, [load])

  useEffect(() => {
    const id = setInterval(() => setNow(new Date()), 1000)
    return () => clearInterval(id)
  }, [])

  async function punch(direction: 'in' | 'out') {
    if (punching) return
    setPunching(true)

    try {
      const coords = await tryCoords()
      const result = direction === 'in' ? await api.checkIn(coords) : await api.checkOut(coords)

      await Haptics.impact({ style: ImpactStyle.Medium }).catch(() => undefined)

      // Punched — so today's nudge for this direction has done its job.
      await cancelTodayReminder(direction)

      onToast(result.message, 'success')
      await load()
    } catch (err: any) {
      onToast(err?.message ?? 'The punch did not go through.', 'error')
    } finally {
      setPunching(false)
    }
  }

  if (loading) return <div className="screen"><Loader label="Getting your day ready…" /></div>

  if (!data) {
    return (
      <div className="screen">
        {error && <Alert tone="error">{error}</Alert>}
        <button className="btn btn--ghost" onClick={() => { setLoading(true); load() }}>Try again</button>
      </div>
    )
  }

  const greeting =
    now.getHours() < 12 ? 'Good morning' : now.getHours() < 17 ? 'Good afternoon' : 'Good evening'
  const firstName = user.full_name.trim().split(/\s+/)[0]

  const clock = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true })
  const [time, meridiem] = clock.split(' ')

  const ready = data.can_check_in || data.can_check_out
  const summary = data.summary

  return (
    <div className="screen">
      <div className="screen__head">
        <div>
          <h1 className="screen__title">{greeting}, {firstName}</h1>
          <p className="screen__sub">
            {data.holiday
              ? `Today is ${data.holiday}`
              : data.is_off_day
                ? 'Today is your weekly off'
                : data.shift
                  ? `Shift ${data.shift.start_label} – ${data.shift.end_label}`
                  : 'No shift assigned yet'}
          </p>
        </div>
      </div>

      {error && <Alert tone="error">{error}</Alert>}

      {data.ip_restricted && (
        <Alert tone="info">Punching is limited to the office network.</Alert>
      )}

      {/* --------------------------------------------------------- punch */}
      <div className="card punch">
        <div className="punch__clock">{time}<span>{meridiem}</span></div>
        <div className="punch__date">
          {now.toLocaleDateString([], { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}
        </div>

        <div className={`dial ${ready && !punching ? 'dial--ready' : ''}`}>
          <div className="dial__ring" />
          <button
            className="dial__btn"
            disabled={!ready || punching}
            onClick={() => punch(data.can_check_in ? 'in' : 'out')}
          >
            {punching ? (
              <>
                <span className="spinner" />
                <span className="dial__hint">Recording…</span>
              </>
            ) : data.can_check_in ? (
              <>
                <span className="dial__icon">→</span>
                <span className="dial__label">Check In</span>
                <span className="dial__hint">Tap to start your day</span>
              </>
            ) : data.can_check_out ? (
              <>
                <span className="dial__icon">←</span>
                <span className="dial__label">Check Out</span>
                <span className="dial__hint">Tap when you finish</span>
              </>
            ) : (
              <>
                <span className="dial__icon">✓</span>
                <span className="dial__label">All done</span>
                <span className="dial__hint">See you tomorrow</span>
              </>
            )}
          </button>
        </div>

        <div className="punch-meta">
          <div className="punch-meta__cell">
            <div className="punch-meta__label">In</div>
            <div className="punch-meta__value">{data.today?.check_in_label ?? '—'}</div>
          </div>
          <div className="punch-meta__cell">
            <div className="punch-meta__label">Out</div>
            <div className="punch-meta__value">{data.today?.check_out_label ?? '—'}</div>
          </div>
          <div className="punch-meta__cell">
            <div className="punch-meta__label">Worked</div>
            <div className="punch-meta__value">{data.today?.worked_label ?? '—'}</div>
          </div>
        </div>

        {data.today?.is_late && (
          <div style={{ marginTop: 12 }}>
            <span className="chip chip--warning">
              Late by {humanMinutes(data.today.late_minutes)}
            </span>
          </div>
        )}
      </div>

      {/* ------------------------------------------------------- summary */}
      <div className="card">
        <p className="card__title">{new Date().toLocaleDateString([], { month: 'long', year: 'numeric' })}</p>

        <div style={{ display: 'flex', alignItems: 'center', gap: 18 }}>
          <Gauge value={summary.attendance_pct} label="Present" />
          <div style={{ flex: 1, display: 'grid', gap: 9 }}>
            <div className="kv"><span>Working days</span><span>{summary.working_days}</span></div>
            <div className="kv"><span>Present</span><span>{summary.present}</span></div>
            <div className="kv"><span>Absent</span><span>{summary.absent}</span></div>
            <div className="kv"><span>Hours worked</span><span>{humanMinutes(summary.worked_minutes)}</span></div>
          </div>
        </div>
      </div>

      <div className="stats" style={{ marginTop: 14 }}>
        <div className="stat">
          <div className="stat__value">{summary.late}</div>
          <div className="stat__label">Late arrivals</div>
          <div className="stat__bar">
            <i style={{
              width: `${summary.working_days ? Math.min(100, (summary.late / summary.working_days) * 100) : 0}%`,
              background: 'var(--amber)',
            }} />
          </div>
        </div>
        <div className="stat">
          <div className="stat__value">{data.leave_balance.remaining}</div>
          <div className="stat__label">Leave days left</div>
          <div className="stat__bar">
            <i style={{
              width: `${data.leave_balance.quota ? (data.leave_balance.remaining / data.leave_balance.quota) * 100 : 0}%`,
              background: 'var(--mint)',
            }} />
          </div>
        </div>
      </div>

      {/* -------------------------------------------------------- recent */}
      <div className="card" style={{ marginTop: 14 }}>
        <p className="card__title">Recent days</p>

        {data.recent.length === 0 ? (
          <Empty icon="🗓">Nothing recorded yet this month.</Empty>
        ) : (
          data.recent.map((day) => (
            <div className="row" key={day.date}>
              <span className="row__dot" style={{ background: `var(--${toneVar(day.tone)})` }} />
              <div className="row__main">
                <div className="row__title">{day.is_today ? 'Today' : day.label}</div>
                <div className="row__sub">{day.status}</div>
              </div>
              <div className="row__right">
                {day.check_in ? (
                  <>
                    {day.check_in} → {day.check_out ?? '—'}
                    <br />
                    <span style={{ opacity: 0.75 }}>{day.worked ?? ''}</span>
                  </>
                ) : (
                  '—'
                )}
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  )
}

function toneVar(tone: string): string {
  switch (tone) {
    case 'success': return 'mint'
    case 'warning': return 'amber'
    case 'danger': return 'rose'
    case 'info': return 'sky'
    case 'primary': return 'violet'
    default: return 'muted'
  }
}
