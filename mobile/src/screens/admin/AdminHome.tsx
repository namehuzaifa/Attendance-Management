import { useCallback, useEffect, useState } from 'react'
import { api, type AdminDashboard, type User } from '../../api'
import { Alert, Empty, Loader, initials } from '../../ui'

export default function AdminHome({
  user,
  onExpired,
  onOpenLeaves,
}: {
  user: User
  onExpired: () => void
  onOpenLeaves: () => void
}) {
  const [data, setData] = useState<AdminDashboard | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const load = useCallback(async () => {
    try {
      const result = await api.adminDashboard()
      setData(result.data)
      setError(null)
    } catch (err: any) {
      if (err?.status === 401) {
        onExpired()
        return
      }
      setError(err?.message ?? 'Could not load the dashboard.')
    } finally {
      setLoading(false)
    }
  }, [onExpired])

  useEffect(() => {
    load()
  }, [load])

  if (loading) return <div className="screen"><Loader label="Loading today…" /></div>

  if (!data) {
    return (
      <div className="screen">
        {error && <Alert tone="error">{error}</Alert>}
        <button className="btn btn--ghost" onClick={() => { setLoading(true); load() }}>Try again</button>
      </div>
    )
  }

  const hour = new Date().getHours()
  const greeting = hour < 12 ? 'Good morning' : hour < 17 ? 'Good afternoon' : 'Good evening'
  const firstName = user.full_name.trim().split(/\s+/)[0]

  const peak = Math.max(1, ...data.trend.map((t) => t.present))

  return (
    <div className="screen">
      <div className="screen__head">
        <div>
          <h1 className="screen__title">{greeting}, {firstName}</h1>
          <p className="screen__sub">{data.label}</p>
        </div>
      </div>

      {error && <Alert tone="error">{error}</Alert>}
      {data.holiday && <Alert tone="info">Today is a holiday — {data.holiday}</Alert>}

      {/* ------------------------------------------------------- turnout */}
      <div className="card">
        <p className="card__title">Today's turnout</p>

        <div style={{ display: 'flex', alignItems: 'baseline', gap: 10, marginBottom: 12 }}>
          <span style={{ fontSize: '2.6rem', fontWeight: 700, lineHeight: 1 }}>
            {data.stats.present}
          </span>
          <span style={{ color: 'var(--muted)' }}>
            of {data.stats.employees} checked in · {data.stats.rate}%
          </span>
        </div>

        <div className="stat__bar" style={{ height: 6 }}>
          <i style={{
            width: `${data.stats.rate}%`,
            background: 'linear-gradient(90deg, var(--violet), var(--sky))',
          }} />
        </div>
      </div>

      <div className="stats stats--3" style={{ marginTop: 14 }}>
        <div className="stat">
          <div className="stat__value" style={{ color: 'var(--amber)' }}>{data.stats.late}</div>
          <div className="stat__label">Late</div>
        </div>
        <div className="stat">
          <div className="stat__value" style={{ color: 'var(--sky)' }}>{data.stats.on_leave}</div>
          <div className="stat__label">On leave</div>
        </div>
        <div className="stat">
          <div className="stat__value" style={{ color: 'var(--rose)' }}>{data.stats.missing}</div>
          <div className="stat__label">Missing</div>
        </div>
      </div>

      {/* --------------------------------------------------- approvals */}
      {data.pending_leaves > 0 && (
        <div className="card" style={{ marginTop: 14 }}>
          <div className="row" style={{ paddingTop: 0 }}>
            <div className="row__main">
              <div className="row__title">{data.pending_leaves} leave request{data.pending_leaves > 1 ? 's' : ''} waiting</div>
              <div className="row__sub">Needs your decision</div>
            </div>
            <button className="btn btn--primary btn--sm" onClick={onOpenLeaves}>Review</button>
          </div>
        </div>
      )}

      {/* ------------------------------------------------------- trend */}
      <div className="card" style={{ marginTop: 14 }}>
        <p className="card__title">Last 14 days</p>
        <div className="spark">
          {data.trend.map((point, i) => (
            <span
              key={point.label}
              className={`spark__bar ${i === data.trend.length - 1 ? 'spark__bar--today' : ''}`}
              style={{ height: `${Math.max(4, (point.present / peak) * 100)}%` }}
              title={`${point.label}: ${point.present} present, ${point.late} late`}
            />
          ))}
        </div>
        <div className="spark__labels">
          <span>{data.trend[0]?.label}</span>
          <span>{data.trend[data.trend.length - 1]?.label}</span>
        </div>
      </div>

      {/* ------------------------------------------------- not checked in */}
      <div className="card" style={{ marginTop: 14 }}>
        <p className="card__title">Not checked in</p>

        {data.missing.length === 0 ? (
          <Empty icon="👍">Everyone expected today has checked in.</Empty>
        ) : (
          data.missing.map((person) => (
            <div className="row" key={person.id}>
              <span className="avatar" style={{ background: 'color-mix(in srgb, var(--rose) 18%, transparent)', color: 'var(--rose)' }}>
                {initials(person.name)}
              </span>
              <div className="row__main">
                <div className="row__title">{person.name}</div>
                <div className="row__sub">{person.department ?? 'No department'}</div>
              </div>
              <span className="chip chip--danger">Absent</span>
            </div>
          ))
        )}
      </div>

      {/* --------------------------------------------------- on leave */}
      {data.on_leave.length > 0 && (
        <div className="card" style={{ marginTop: 14 }}>
          <p className="card__title">On leave today</p>
          {data.on_leave.map((leave) => (
            <div className="row" key={leave.id}>
              <span className="avatar" style={{ background: 'color-mix(in srgb, var(--sky) 18%, transparent)', color: 'var(--sky)' }}>
                {initials(leave.name)}
              </span>
              <div className="row__main">
                <div className="row__title">{leave.name}</div>
                <div className="row__sub">{leave.type} leave</div>
              </div>
              <div className="row__right">until {leave.until}</div>
            </div>
          ))}
        </div>
      )}

      {/* ------------------------------------------------- recent punches */}
      <div className="card" style={{ marginTop: 14 }}>
        <p className="card__title">Latest punches</p>

        {data.recent_punches.length === 0 ? (
          <Empty icon="🕐">Nobody has punched in yet today.</Empty>
        ) : (
          data.recent_punches.map((punch) => (
            <div className="row" key={punch.id}>
              <span className="avatar">{initials(punch.name)}</span>
              <div className="row__main">
                <div className="row__title">{punch.name}</div>
                <div className="row__sub">
                  <span className={`chip chip--${punch.is_late ? 'warning' : 'success'}`}
                        style={{ padding: '1px 8px', fontSize: '0.68rem' }}>
                    {punch.status}
                  </span>
                </div>
              </div>
              <div className="row__right">
                {punch.check_in} → {punch.check_out ?? '—'}
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  )
}
