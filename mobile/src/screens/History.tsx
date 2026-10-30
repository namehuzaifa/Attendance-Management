import { useCallback, useEffect, useState } from 'react'
import { api, type DayRow, type Summary } from '../api'
import { Alert, Empty, Gauge, Loader, humanMinutes, monthKey, shiftMonth } from '../ui'

export default function History({ onExpired }: { onExpired: () => void }) {
  const [month, setMonth] = useState(() => monthKey(new Date()))
  const [days, setDays] = useState<DayRow[]>([])
  const [summary, setSummary] = useState<Summary | null>(null)
  const [label, setLabel] = useState('')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const load = useCallback(async (key: string) => {
    setLoading(true)
    try {
      const result = await api.month(key)
      setDays(result.data.days)
      setSummary(result.data.summary)
      setLabel(result.data.label)
      setError(null)
    } catch (err: any) {
      if (err?.status === 401) {
        onExpired()
        return
      }
      setError(err?.message ?? 'Could not load that month.')
    } finally {
      setLoading(false)
    }
  }, [onExpired])

  useEffect(() => {
    load(month)
  }, [month, load])

  const isCurrentMonth = month === monthKey(new Date())

  return (
    <div className="screen">
      <div className="screen__head">
        <div>
          <h1 className="screen__title">Attendance</h1>
          <p className="screen__sub">{label || 'Your month, day by day'}</p>
        </div>

        <div className="month-nav">
          <button className="month-nav__btn" onClick={() => setMonth(shiftMonth(month, -1))} aria-label="Previous month">‹</button>
          <button
            className="month-nav__btn"
            onClick={() => setMonth(shiftMonth(month, 1))}
            disabled={isCurrentMonth}
            aria-label="Next month"
          >›</button>
        </div>
      </div>

      {error && <Alert tone="error">{error}</Alert>}

      {loading ? (
        <Loader />
      ) : (
        <>
          {summary && (
            <>
              <div className="card">
                <div style={{ display: 'flex', alignItems: 'center', gap: 18 }}>
                  <Gauge value={summary.attendance_pct} label="Present" />
                  <div style={{ flex: 1 }}>
                    <div className="kv"><span>Working days</span><span>{summary.working_days}</span></div>
                    <div className="kv"><span>Present</span><span>{summary.present}</span></div>
                    <div className="kv"><span>Absent</span><span>{summary.absent}</span></div>
                    <div className="kv"><span>On leave</span><span>{summary.leave}</span></div>
                  </div>
                </div>
              </div>

              <div className="card">
                <p className="card__title">Hours</p>
                <div className="kv"><span>Worked</span><span>{humanMinutes(summary.worked_minutes)}</span></div>
                <div className="kv"><span>Required</span><span>{humanMinutes(summary.required_minutes)}</span></div>
                <div className="kv">
                  <span>Balance</span>
                  <span style={{
                    color: summary.worked_minutes - summary.required_minutes >= 0 ? 'var(--mint)' : 'var(--rose)',
                  }}>
                    {summary.worked_minutes - summary.required_minutes >= 0 ? '+' : '−'}
                    {humanMinutes(Math.abs(summary.worked_minutes - summary.required_minutes))}
                  </span>
                </div>

                <div style={{ display: 'flex', gap: 8, marginTop: 12, flexWrap: 'wrap' }}>
                  <span className="chip chip--warning">{summary.late} late</span>
                  <span className="chip chip--info">{summary.early_out} early out</span>
                  <span className="chip chip--danger">{summary.short_hour} short hour</span>
                  <span className="chip chip--secondary">{summary.holiday} holidays</span>
                </div>
              </div>
            </>
          )}

          <div className="card">
            <p className="card__title">Daily record</p>

            {days.length === 0 ? (
              <Empty icon="🗓">Nothing recorded for this month.</Empty>
            ) : (
              days.map((day) => (
                <div className="row" key={day.date}>
                  <span className="row__dot" style={{ background: `var(--${toneVar(day.tone)})` }} />
                  <div className="row__main">
                    <div className="row__title">{day.is_today ? `Today · ${day.label}` : day.label}</div>
                    <div className="row__sub">
                      <span className={`chip chip--${day.tone}`} style={{ padding: '1px 8px', fontSize: '0.68rem' }}>
                        {day.status}
                      </span>
                      {day.late && <span className="chip chip--warning" style={{ marginLeft: 5, padding: '1px 8px', fontSize: '0.68rem' }}>Late</span>}
                      {day.short_hour && <span className="chip chip--danger" style={{ marginLeft: 5, padding: '1px 8px', fontSize: '0.68rem' }}>Short</span>}
                    </div>
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
        </>
      )}
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
