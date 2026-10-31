import { useCallback, useEffect, useState } from 'react'
import { api, type AdminAttendanceRow } from '../../api'
import {
  type Coords,
  directionsUrl,
  distanceMetres,
  embedUrl,
  formatCoords,
  formatDistance,
  openUrl,
} from '../../maps'
import { Alert, Empty, Loader, Sheet, initials, todayIso } from '../../ui'

export default function AdminAttendance({ onExpired }: { onExpired: () => void }) {
  const [date, setDate] = useState(todayIso())
  const [rows, setRows] = useState<AdminAttendanceRow[]>([])
  const [label, setLabel] = useState('')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [viewing, setViewing] = useState<AdminAttendanceRow | null>(null)

  const load = useCallback(async (day: string) => {
    setLoading(true)

    try {
      const result = await api.adminAttendance(day)
      setRows(result.data.records)
      setLabel(result.data.label)
      setError(null)
    } catch (err: any) {
      if (err?.status === 401) {
        onExpired()
        return
      }
      setError(err?.message ?? 'Could not load that day.')
    } finally {
      setLoading(false)
    }
  }, [onExpired])

  useEffect(() => {
    load(date)
  }, [date, load])

  function shiftDay(days: number) {
    const next = new Date(date)
    next.setDate(next.getDate() + days)

    const iso = next.toISOString().slice(0, 10)
    if (iso <= todayIso()) setDate(iso)
  }

  return (
    <div className="screen">
      <div className="screen__head">
        <div>
          <h1 className="screen__title">Attendance</h1>
          <p className="screen__sub">{label || 'Pick a day'}</p>
        </div>

        <div className="month-nav">
          <button className="month-nav__btn" onClick={() => shiftDay(-1)} aria-label="Previous day">‹</button>
          <button
            className="month-nav__btn"
            onClick={() => shiftDay(1)}
            disabled={date >= todayIso()}
            aria-label="Next day"
          >›</button>
        </div>
      </div>

      {error && <Alert tone="error">{error}</Alert>}

      <div className="field">
        <input type="date" value={date} max={todayIso()} onChange={(e) => setDate(e.target.value)} />
      </div>

      {loading ? (
        <Loader />
      ) : rows.length === 0 ? (
        <div className="card">
          <Empty icon="🗓">No attendance recorded on this day.</Empty>
        </div>
      ) : (
        <div className="card">
          {rows.map((row) => {
            const hasLocation = row.check_in_location || row.check_out_location

            return (
              <div className="row" key={row.id}>
                <span className="avatar">{initials(row.name)}</span>

                <div className="row__main">
                  <div className="row__title">{row.name}</div>
                  <div className="row__sub">
                    {row.check_in ?? '—'} → {row.check_out ?? '—'}
                    {row.worked ? ` · ${row.worked}` : ''}
                  </div>
                </div>

                <div style={{ display: 'grid', gap: 6, justifyItems: 'end' }}>
                  <span className={`chip chip--${row.is_late ? 'warning' : 'success'}`}>
                    {row.is_late ? 'Late' : 'On time'}
                  </span>

                  {hasLocation && (
                    <button className="btn btn--ghost btn--sm" onClick={() => setViewing(row)}>
                      📍 Location
                    </button>
                  )}
                </div>
              </div>
            )
          })}
        </div>
      )}

      {viewing && <LocationSheet row={viewing} onClose={() => setViewing(null)} />}
    </div>
  )
}

function LocationSheet({ row, onClose }: { row: AdminAttendanceRow; onClose: () => void }) {
  const inAt = row.check_in_location
  const outAt = row.check_out_location
  const gap = inAt && outAt ? distanceMetres(inAt, outAt) : null

  return (
    <Sheet title={`${row.name} — punch locations`} onClose={onClose}>
      {gap !== null && (
        <Alert tone={gap > 500 ? 'error' : 'info'}>
          Check-in and check-out were {formatDistance(gap)}.
          {gap > 500 && ' That is a long way for one shift — worth a look.'}
        </Alert>
      )}

      <MapBlock title="Checked in" time={row.check_in} point={inAt} />
      <MapBlock title="Checked out" time={row.check_out} point={outAt} />

      {inAt && outAt && (
        <a
          className="btn btn--ghost"
          href={directionsUrl(inAt, outAt)}
          target="_blank"
          rel="noreferrer"
          style={{ marginTop: 6 }}
        >
          View both on one map
        </a>
      )}
    </Sheet>
  )
}

function MapBlock({
  title,
  time,
  point,
}: {
  title: string
  time: string | null
  point: Coords | null
}) {
  if (!point) {
    return (
      <div className="card" style={{ marginBottom: 14 }}>
        <p className="card__title">{title}</p>
        <p style={{ color: 'var(--muted)', fontSize: '0.85rem', margin: 0 }}>
          {time ? 'No location was shared for this punch.' : 'Not punched.'}
        </p>
      </div>
    )
  }

  return (
    <div className="card" style={{ marginBottom: 14 }}>
      <p className="card__title">{title}{time ? ` · ${time}` : ''}</p>

      <iframe
        title={title}
        src={embedUrl(point)}
        loading="lazy"
        referrerPolicy="no-referrer-when-downgrade"
        style={{
          width: '100%',
          height: 190,
          border: '1px solid var(--stroke)',
          borderRadius: 14,
          display: 'block',
        }}
      />

      <div className="kv" style={{ marginTop: 10 }}>
        <span>Coordinates</span>
        <span>{formatCoords(point)}</span>
      </div>

      <a className="btn btn--ghost btn--sm" href={openUrl(point)} target="_blank" rel="noreferrer"
         style={{ marginTop: 10, width: '100%' }}>
        Open in Google Maps
      </a>
    </div>
  )
}
