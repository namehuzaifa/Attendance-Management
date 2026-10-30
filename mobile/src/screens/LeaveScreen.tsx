import { useCallback, useEffect, useState, type FormEvent } from 'react'
import { api, type Leave } from '../api'
import { Alert, Empty, Loader, Sheet, todayIso } from '../ui'

type Balance = { quota: number; used: number; remaining: number; pending: number }

export default function LeaveScreen({
  onToast,
  onExpired,
}: {
  onToast: (message: string, tone?: 'success' | 'error') => void
  onExpired: () => void
}) {
  const [leaves, setLeaves] = useState<Leave[]>([])
  const [types, setTypes] = useState<string[]>([])
  const [balance, setBalance] = useState<Balance | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [sheetOpen, setSheetOpen] = useState(false)

  const load = useCallback(async () => {
    try {
      const result = await api.leaves()
      setLeaves(result.data.leaves)
      setTypes(result.data.types)
      setBalance(result.data.balance)
      setError(null)
    } catch (err: any) {
      if (err?.status === 401) {
        onExpired()
        return
      }
      setError(err?.message ?? 'Could not load your leave.')
    } finally {
      setLoading(false)
    }
  }, [onExpired])

  useEffect(() => {
    load()
  }, [load])

  async function cancel(leave: Leave) {
    try {
      const result = await api.cancelLeave(leave.id)
      onToast(result.message, 'success')
      await load()
    } catch (err: any) {
      onToast(err?.message ?? 'Could not cancel that request.', 'error')
    }
  }

  if (loading) return <div className="screen"><Loader /></div>

  return (
    <div className="screen">
      <div className="screen__head">
        <div>
          <h1 className="screen__title">Leave</h1>
          <p className="screen__sub">Request time off and track approvals</p>
        </div>
        <button className="btn btn--primary btn--sm" onClick={() => setSheetOpen(true)}>+ Apply</button>
      </div>

      {error && <Alert tone="error">{error}</Alert>}

      {balance && (
        <div className="card">
          <p className="card__title">Balance {new Date().getFullYear()}</p>
          <div className="stats">
            <div className="stat">
              <div className="stat__value" style={{ color: 'var(--mint)' }}>{balance.remaining}</div>
              <div className="stat__label">Days remaining</div>
            </div>
            <div className="stat">
              <div className="stat__value">{balance.used}</div>
              <div className="stat__label">Days used</div>
            </div>
          </div>

          <div className="stat__bar" style={{ marginTop: 14 }}>
            <i style={{
              width: `${balance.quota ? Math.min(100, (balance.used / balance.quota) * 100) : 0}%`,
              background: 'linear-gradient(90deg, var(--violet), var(--sky))',
            }} />
          </div>
          <p className="field__hint" style={{ marginTop: 8 }}>
            {balance.used} of {balance.quota} days used
            {balance.pending > 0 ? ` · ${balance.pending} day(s) awaiting approval` : ''}
          </p>
        </div>
      )}

      <div className="card">
        <p className="card__title">My requests</p>

        {leaves.length === 0 ? (
          <Empty icon="🌴">You have not requested any leave yet.</Empty>
        ) : (
          leaves.map((leave) => (
            <div className="row" key={leave.id}>
              <div className="row__main">
                <div className="row__title">{leave.type_label} · {leave.days}d</div>
                <div className="row__sub">{leave.range_label}</div>
                {leave.admin_notes && (
                  <div className="row__sub" style={{ opacity: 0.85 }}>Note: {leave.admin_notes}</div>
                )}
              </div>
              <div style={{ textAlign: 'right' }}>
                <span className={`chip chip--${statusTone(leave.status)}`}>{leave.status}</span>
                {leave.status === 'pending' && (
                  <div style={{ marginTop: 7 }}>
                    <button className="btn btn--danger btn--sm" onClick={() => cancel(leave)}>Cancel</button>
                  </div>
                )}
              </div>
            </div>
          ))
        )}
      </div>

      {sheetOpen && (
        <ApplySheet
          types={types}
          onClose={() => setSheetOpen(false)}
          onDone={async (message) => {
            setSheetOpen(false)
            onToast(message, 'success')
            await load()
          }}
          onError={(message) => onToast(message, 'error')}
        />
      )}
    </div>
  )
}

function ApplySheet({
  types,
  onClose,
  onDone,
  onError,
}: {
  types: string[]
  onClose: () => void
  onDone: (message: string) => void
  onError: (message: string) => void
}) {
  const [type, setType] = useState(types[0] ?? 'casual')
  const [start, setStart] = useState(todayIso())
  const [end, setEnd] = useState(todayIso())
  const [reason, setReason] = useState('')
  const [busy, setBusy] = useState(false)

  async function submit(event: FormEvent) {
    event.preventDefault()
    setBusy(true)

    try {
      const result = await api.applyLeave({ type, start_date: start, end_date: end, reason })
      onDone(result.message)
    } catch (err: any) {
      onError(err?.message ?? 'Could not submit the request.')
    } finally {
      setBusy(false)
    }
  }

  return (
    <Sheet title="Apply for leave" onClose={onClose}>
      <form onSubmit={submit}>
        <div className="field">
          <label htmlFor="leaveType">Type</label>
          <select id="leaveType" value={type} onChange={(e) => setType(e.target.value)}>
            {types.map((t) => (
              <option key={t} value={t}>{t.charAt(0).toUpperCase() + t.slice(1)}</option>
            ))}
          </select>
        </div>

        <div className="grid-2">
          <div className="field">
            <label htmlFor="leaveFrom">From</label>
            <input
              id="leaveFrom"
              type="date"
              value={start}
              onChange={(e) => {
                setStart(e.target.value)
                // Keep the range valid rather than letting the server reject it.
                if (e.target.value > end) setEnd(e.target.value)
              }}
              required
            />
          </div>
          <div className="field">
            <label htmlFor="leaveTo">To</label>
            <input id="leaveTo" type="date" value={end} min={start}
                   onChange={(e) => setEnd(e.target.value)} required />
          </div>
        </div>

        <div className="field">
          <label htmlFor="leaveReason">Reason</label>
          <textarea
            id="leaveReason"
            rows={3}
            placeholder="Tell your manager why"
            value={reason}
            onChange={(e) => setReason(e.target.value)}
            required
          />
          <p className="field__hint">Weekly offs and public holidays are not deducted.</p>
        </div>

        <button className="btn btn--primary" type="submit" disabled={busy}>
          {busy && <span className="spinner" />}
          Submit request
        </button>
      </form>
    </Sheet>
  )
}

function statusTone(status: Leave['status']): string {
  switch (status) {
    case 'approved': return 'success'
    case 'rejected': return 'danger'
    case 'cancelled': return 'secondary'
    default: return 'warning'
  }
}
