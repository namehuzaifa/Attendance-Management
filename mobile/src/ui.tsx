import { useEffect, useState, type ReactNode } from 'react'

/* ------------------------------------------------------------------ icons */

type IconProps = { d: string }

function Icon({ d }: IconProps) {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"
         strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d={d} />
    </svg>
  )
}

export const icons = {
  home: 'M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z',
  calendar: 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z',
  leave: 'M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4zM6 1v3M10 1v3M14 1v3',
  user: 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z',
  refresh: 'M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15',
}

export function TabIcon({ name }: { name: keyof typeof icons }) {
  return <Icon d={icons[name]} />
}

/* ------------------------------------------------------------------ toast */

export type ToastState = { message: string; tone: 'success' | 'error' } | null

export function Toast({ toast }: { toast: ToastState }) {
  if (!toast) return null
  return <div className={`toast toast--${toast.tone}`}>{toast.message}</div>
}

export function useToast() {
  const [toast, setToast] = useState<ToastState>(null)

  useEffect(() => {
    if (!toast) return
    const id = setTimeout(() => setToast(null), 3200)
    return () => clearTimeout(id)
  }, [toast])

  return {
    toast,
    show: (message: string, tone: 'success' | 'error' = 'success') => setToast({ message, tone }),
  }
}

/* ----------------------------------------------------------------- pieces */

export function Loader({ label = 'Loading…' }: { label?: string }) {
  return (
    <div className="loader">
      <div className="spinner" />
      <span>{label}</span>
    </div>
  )
}

export function Empty({ icon, children }: { icon: string; children: ReactNode }) {
  return (
    <div className="empty">
      <span className="empty__icon">{icon}</span>
      {children}
    </div>
  )
}

export function Alert({ tone, children }: { tone: 'error' | 'success' | 'info'; children: ReactNode }) {
  return <div className={`alert alert--${tone}`}>{children}</div>
}

export function Gauge({ value, label }: { value: number; label: string }) {
  return (
    <div className="gauge" style={{ ['--value' as string]: String(value) }}>
      <div className="gauge__inner">
        <div>
          <div className="gauge__value">{value}%</div>
          <div className="gauge__label">{label}</div>
        </div>
      </div>
    </div>
  )
}

export function Sheet({
  title,
  onClose,
  children,
}: {
  title: string
  onClose: () => void
  children: ReactNode
}) {
  // A tap on the backdrop closes; taps inside must not bubble up to it.
  return (
    <div className="sheet" onClick={onClose}>
      <div className="sheet__panel" onClick={(e) => e.stopPropagation()}>
        <div className="sheet__grab" />
        <h3 className="sheet__title">{title}</h3>
        {children}
      </div>
    </div>
  )
}

/* ------------------------------------------------------------------ utils */

export const toneClass = (tone: string) => `chip chip--${tone}`

export function initials(name: string): string {
  const parts = name.trim().split(/\s+/)
  const first = parts[0]?.[0] ?? ''
  const last = parts.length > 1 ? parts[parts.length - 1][0] : ''
  return (first + last).toUpperCase()
}

export function humanMinutes(minutes: number | null | undefined): string {
  if (minutes === null || minutes === undefined) return '—'
  return `${Math.floor(minutes / 60)}h ${minutes % 60}m`
}

export function monthKey(date: Date): string {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
}

export function shiftMonth(key: string, delta: number): string {
  const [year, month] = key.split('-').map(Number)
  const date = new Date(year, month - 1 + delta, 1)
  return monthKey(date)
}

export function todayIso(): string {
  const now = new Date()
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`
}
