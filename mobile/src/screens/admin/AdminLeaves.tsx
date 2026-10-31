import { useCallback, useEffect, useState } from 'react'
import { api, type AdminLeave } from '../../api'
import { Alert, Empty, Loader, Sheet, initials } from '../../ui'

const FILTERS = ['pending', 'approved', 'rejected', 'all'] as const

export default function AdminLeaves({
  onToast,
  onExpired,
}: {
  onToast: (message: string, tone?: 'success' | 'error') => void
  onExpired: () => void
}) {
  const [filter, setFilter] = useState<(typeof FILTERS)[number]>('pending')
  const [leaves, setLeaves] = useState<AdminLeave[]>([])
  const [counts, setCounts] = useState({ pending: 0, approved: 0, rejected: 0 })
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [deciding, setDeciding] = useState<{ leave: AdminLeave; decision: 'approved' | 'rejected' } | null>(null)

  const load = useCallback(async (status: string) => {
    setLoading(true)

    try {
      const result = await api.adminLeaves(status)
      setLeaves(result.data.leaves)
      setCounts(result.data.counts)
      setError(null)
    } catch (err: any) {
      if (err?.status === 401) {
        onExpired()
        return
      }
      setError(err?.message ?? 'Could not load leave requests.')
    } finally {
      setLoading(false)
    }
  }, [onExpired])

  useEffect(() => {
    load(filter)
  }, [filter, load])

  return (
    <div className="screen">
      <div className="screen__head">
        <div>
          <h1 className="screen__title">Leave requests</h1>
          <p className="screen__sub">{counts.pending} waiting for a decision</p>
        </div>
      </div>

      {error && <Alert tone="error">{error}</Alert>}

      <div className="segment" style={{ marginBottom: 14 }}>
        {FILTERS.map((item) => (
          <button
            key={item}
            className={`segment__item ${filter === item ? 'segment__item--active' : ''}`}
            onClick={() => setFilter(item)}
          >
            {item.charAt(0).toUpperCase() + item.slice(1)}
          </button>
        ))}
      </div>

      {loading ? (
        <Loader />
      ) : leaves.length === 0 ? (
        <div className="card">
          <Empty icon="📭">No {filter === 'all' ? '' : filter} leave requests.</Empty>
        </div>
      ) : (
        leaves.map((leave) => (
          <div className="card" key={leave.id} style={{ marginBottom: 12 }}>
            <div className="row" style={{ paddingTop: 0, borderBottom: 'none' }}>
              <span className="avatar">{initials(leave.employee)}</span>
              <div className="row__main">
                <div className="row__title">{leave.employee}</div>
                <div className="row__sub">{leave.type_label} · {leave.days} day{leave.days > 1 ? 's' : ''}</div>
              </div>
              <span className={`chip chip--${statusTone(leave.status)}`}>{leave.status}</span>
            </div>

            <div className="kv"><span>Dates</span><span>{leave.range_label}</span></div>
            <div className="kv"><span>Applied</span><span>{leave.applied_on}</span></div>

            <p style={{ color: 'var(--muted)', fontSize: '0.85rem', margin: '10px 0 0' }}>
              {leave.reason}
            </p>

            {leave.admin_notes && (
              <p style={{ color: 'var(--muted)', fontSize: '0.8rem', margin: '6px 0 0' }}>
                Note: {leave.admin_notes}{leave.approver ? ` — ${leave.approver}` : ''}
              </p>
            )}

            {leave.status === 'pending' && (
              <div style={{ display: 'flex', gap: 9, marginTop: 14 }}>
                <button className="btn btn--success" onClick={() => setDeciding({ leave, decision: 'approved' })}>
                  Approve
                </button>
                <button className="btn btn--danger" onClick={() => setDeciding({ leave, decision: 'rejected' })}>
                  Reject
                </button>
              </div>
            )}
          </div>
        ))
      )}

      {deciding && (
        <DecisionSheet
          leave={deciding.leave}
          decision={deciding.decision}
          onClose={() => setDeciding(null)}
          onDone={async (message) => {
            setDeciding(null)
            onToast(message, 'success')
            await load(filter)
          }}
          onError={(message) => onToast(message, 'error')}
        />
      )}
    </div>
  )
}

function DecisionSheet({
  leave,
  decision,
  onClose,
  onDone,
  onError,
}: {
  leave: AdminLeave
  decision: 'approved' | 'rejected'
  onClose: () => void
  onDone: (message: string) => void
  onError: (message: string) => void
}) {
  const [notes, setNotes] = useState('')
  const [busy, setBusy] = useState(false)

  async function submit() {
    setBusy(true)

    try {
      const result = await api.adminDecideLeave(leave.id, decision, notes || undefined)
      onDone(result.message)
    } catch (err: any) {
      onError(err?.message ?? 'Could not save that decision.')
    } finally {
      setBusy(false)
    }
  }

  return (
    <Sheet
      title={`${decision === 'approved' ? 'Approve' : 'Reject'} ${leave.employee}'s leave`}
      onClose={onClose}
    >
      <div className="card" style={{ marginBottom: 14 }}>
        <div className="kv"><span>Type</span><span>{leave.type_label}</span></div>
        <div className="kv"><span>Dates</span><span>{leave.range_label}</span></div>
        <div className="kv"><span>Days</span><span>{leave.days}</span></div>
      </div>

      <div className="field">
        <label htmlFor="decisionNotes">Note for the employee (optional)</label>
        <textarea
          id="decisionNotes"
          rows={3}
          value={notes}
          onChange={(e) => setNotes(e.target.value)}
          placeholder={decision === 'approved' ? 'Approved, enjoy your break' : 'Why it cannot be approved'}
        />
      </div>

      <button
        className={decision === 'approved' ? 'btn btn--primary' : 'btn btn--danger'}
        onClick={submit}
        disabled={busy}
      >
        {busy && <span className="spinner" />}
        {decision === 'approved' ? 'Approve request' : 'Reject request'}
      </button>
    </Sheet>
  )
}

function statusTone(status: AdminLeave['status']): string {
  switch (status) {
    case 'approved': return 'success'
    case 'rejected': return 'danger'
    case 'cancelled': return 'secondary'
    default: return 'warning'
  }
}
