import { useEffect, useState, type FormEvent } from 'react'
import { api, type Holiday, type User } from '../api'
import { syncReminders } from '../notifications'
import { THEME_CHOICES, type ThemeChoice } from '../theme'
import { Empty, Sheet, initials } from '../ui'
import Logo from '../Logo'
import Reminders from './Reminders'

export default function Profile({
  user,
  theme,
  onThemeChange,
  onToast,
  onSignOut,
}: {
  user: User
  theme: ThemeChoice
  onThemeChange: (choice: ThemeChoice) => void
  onToast: (message: string, tone?: 'success' | 'error') => void
  onSignOut: () => void
}) {
  const [holidays, setHolidays] = useState<Holiday[]>([])
  const [passwordSheet, setPasswordSheet] = useState(false)
  const [reminderSheet, setReminderSheet] = useState(false)

  const isAdmin = user.role === 'admin'

  useEffect(() => {
    api
      .holidays(new Date().getFullYear())
      .then((result) => setHolidays(result.data.filter((h) => !h.is_past).slice(0, 6)))
      .catch(() => setHolidays([]))
  }, [])

  async function signOut() {
    // Drop the local session even if the server call fails, otherwise a
    // network blip leaves someone stuck signed in on a shared handset.
    try {
      await api.logout()
    } catch {
      /* ignored on purpose */
    }
    onSignOut()
  }

  return (
    <div className="screen">
      <div className="screen__head">
        <h1 className="screen__title">Profile</h1>
        <Logo size={34} />
      </div>

      <div className="card">
        <div className="profile__head">
          <div className="profile__avatar">{initials(user.full_name)}</div>
          <div style={{ minWidth: 0 }}>
            <div style={{ fontSize: '1.12rem', fontWeight: 700 }}>{user.full_name}</div>
            <div style={{ color: 'var(--muted)', fontSize: '0.85rem', overflow: 'hidden', textOverflow: 'ellipsis' }}>
              {user.email}
            </div>
            <span className={`chip chip--${isAdmin ? 'danger' : 'primary'}`} style={{ marginTop: 7 }}>
              {isAdmin ? 'Administrator' : user.designation ?? 'Employee'}
            </span>
          </div>
        </div>
      </div>

      {/* ---------------------------------------------------- appearance */}
      <div className="card">
        <p className="card__title">Appearance</p>

        <div className="segment">
          {THEME_CHOICES.map((choice) => (
            <button
              key={choice.value}
              className={`segment__item ${theme === choice.value ? 'segment__item--active' : ''}`}
              onClick={() => onThemeChange(choice.value)}
            >
              {choice.label}
            </button>
          ))}
        </div>

        <p className="field__hint" style={{ marginTop: 9 }}>
          {THEME_CHOICES.find((c) => c.value === theme)?.hint}
        </p>
      </div>

      {!isAdmin && (
        <>
          <div className="card">
            <p className="card__title">Employment</p>
            <div className="kv"><span>Employee code</span><span>{user.employee_code ?? '—'}</span></div>
            <div className="kv"><span>Department</span><span>{user.department ?? '—'}</span></div>
            <div className="kv"><span>Designation</span><span>{user.designation ?? '—'}</span></div>
            <div className="kv"><span>Reports to</span><span>{user.manager ?? '—'}</span></div>
            <div className="kv"><span>Joined</span><span>{user.joining_date ?? '—'}</span></div>
            <div className="kv"><span>Phone</span><span>{user.phone ?? '—'}</span></div>
          </div>

          {user.shift && (
            <div className="card">
              <p className="card__title">Shift</p>
              <div className="kv"><span>Name</span><span>{user.shift.name}</span></div>
              <div className="kv"><span>Timing</span><span>{user.shift.start_time} – {user.shift.end_time}</span></div>
              <div className="kv"><span>Grace period</span><span>{user.shift.grace_period} min</span></div>
              <div className="kv"><span>Weekly off</span><span>{user.shift.off_days.join(', ')}</span></div>
            </div>
          )}
        </>
      )}

      <div className="card">
        <p className="card__title">Upcoming holidays</p>
        {holidays.length === 0 ? (
          <Empty icon="🎉">No holidays scheduled.</Empty>
        ) : (
          holidays.map((holiday) => (
            <div className="row" key={holiday.id}>
              <div className="row__main">
                <div className="row__title">{holiday.title}</div>
                <div className="row__sub">{holiday.label}</div>
              </div>
            </div>
          ))
        )}
      </div>

      <div style={{ display: 'grid', gap: 11, marginTop: 16 }}>
        {!isAdmin && (
          <button className="btn btn--ghost" onClick={() => setReminderSheet(true)}>
            🔔 Attendance reminders
          </button>
        )}
        <button className="btn btn--ghost" onClick={() => setPasswordSheet(true)}>Change password</button>
        <button className="btn btn--danger" onClick={signOut}>Sign out</button>
      </div>

      <p className="field__hint" style={{ textAlign: 'center', marginTop: 18 }}>
        HRMS · version 1.1.0
      </p>

      {reminderSheet && (
        <Reminders
          shift={user.shift}
          onClose={() => setReminderSheet(false)}
          onSaved={async () => {
            // Re-read today's punches so a reminder already answered today
            // is not scheduled again.
            let checkedInToday = false
            let checkedOutToday = false

            try {
              const today = await api.today()
              checkedInToday = Boolean(today.data.today?.check_in)
              checkedOutToday = Boolean(today.data.today?.check_out)
            } catch {
              // Fall back to scheduling both; worst case is one extra nudge.
            }

            await syncReminders(user.shift, { checkedInToday, checkedOutToday })
          }}
          onToast={onToast}
        />
      )}

      {passwordSheet && (
        <PasswordSheet
          onClose={() => setPasswordSheet(false)}
          onDone={(message) => {
            setPasswordSheet(false)
            onToast(message, 'success')
          }}
          onError={(message) => onToast(message, 'error')}
        />
      )}
    </div>
  )
}

function PasswordSheet({
  onClose,
  onDone,
  onError,
}: {
  onClose: () => void
  onDone: (message: string) => void
  onError: (message: string) => void
}) {
  const [current, setCurrent] = useState('')
  const [next, setNext] = useState('')
  const [confirm, setConfirm] = useState('')
  const [busy, setBusy] = useState(false)

  async function submit(event: FormEvent) {
    event.preventDefault()

    if (next !== confirm) {
      onError('The new passwords do not match.')
      return
    }

    setBusy(true)

    try {
      const result = await api.changePassword(current, next)
      onDone(result.message)
    } catch (err: any) {
      onError(err?.message ?? 'Could not change your password.')
    } finally {
      setBusy(false)
    }
  }

  return (
    <Sheet title="Change password" onClose={onClose}>
      <form onSubmit={submit}>
        <div className="field">
          <label htmlFor="currentPassword">Current password</label>
          <input id="currentPassword" type="password" value={current}
                 onChange={(e) => setCurrent(e.target.value)} required />
        </div>
        <div className="field">
          <label htmlFor="nextPassword">New password</label>
          <input id="nextPassword" type="password" value={next}
                 onChange={(e) => setNext(e.target.value)} required />
          <p className="field__hint">At least 8 characters.</p>
        </div>
        <div className="field">
          <label htmlFor="confirmPassword">Confirm new password</label>
          <input id="confirmPassword" type="password" value={confirm}
                 onChange={(e) => setConfirm(e.target.value)} required />
        </div>

        <button className="btn btn--primary" type="submit" disabled={busy}>
          {busy && <span className="spinner" />}
          Update password
        </button>
      </form>
    </Sheet>
  )
}
