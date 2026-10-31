import { Preferences } from '@capacitor/preferences'

/**
 * Point the app at a different backend by rebuilding with VITE_API_BASE set,
 * e.g. `VITE_API_BASE=http://10.0.2.2/api npm run apk` for an emulator
 * talking to the machine's local Laragon site.
 */
export const API_BASE: string =
  (import.meta.env.VITE_API_BASE as string | undefined) ?? 'https://hrms.iwork4sindh.com/api'

const TOKEN_KEY = 'hrms.token'
const USER_KEY = 'hrms.user'

export type Shift = {
  name: string
  start_time: string
  end_time: string
  grace_period: number
  off_days: string[]
}

export type User = {
  id: number
  full_name: string
  employee_code: string | null
  email: string
  phone: string | null
  role: string
  avatar_url: string
  joining_date: string | null
  is_active: boolean
  department: string | null
  designation: string | null
  manager: string | null
  shift: Shift | null
}

export type AttendanceRecord = {
  id: number
  date: string
  check_in: string | null
  check_out: string | null
  check_in_label: string | null
  check_out_label: string | null
  status: string
  is_late: boolean
  is_early_out: boolean
  is_short_hour: boolean
  late_minutes: number | null
  worked_minutes: number | null
  worked_label: string | null
  source: string
}

export type DayRow = {
  date: string
  label: string
  day: string
  is_today: boolean
  status: string
  tone: 'success' | 'danger' | 'warning' | 'info' | 'primary' | 'secondary'
  check_in: string | null
  check_out: string | null
  worked: string | null
  late: boolean
  early_out: boolean
  short_hour: boolean
}

export type Summary = {
  working_days: number
  present: number
  absent: number
  late: number
  early_out: number
  short_hour: number
  leave: number
  holiday: number
  weekly_off: number
  worked_minutes: number
  required_minutes: number
  attendance_pct: number
}

export type HomePayload = {
  server_time: string
  business_date: string
  today: AttendanceRecord | null
  shift: {
    start: string
    end: string
    start_label: string
    end_label: string
    grace_period: number
    total_minutes: number
  } | null
  can_check_in: boolean
  can_check_out: boolean
  is_off_day: boolean
  holiday: string | null
  ip_restricted: boolean
  summary: Summary
  recent: DayRow[]
  leave_balance: { quota: number; used: number; remaining: number }
  pending_leaves: number
}

export type Leave = {
  id: number
  type: string
  type_label: string
  start_date: string
  end_date: string
  range_label: string
  days: number
  reason: string
  status: 'pending' | 'approved' | 'rejected' | 'cancelled'
  admin_notes: string | null
  approver: string | null
  acted_at: string | null
  created_at: string
}

export type Holiday = {
  id: number
  title: string
  date: string
  label: string
  description: string | null
  is_past: boolean
}

export type AdminDashboard = {
  date: string
  label: string
  holiday: string | null
  stats: {
    employees: number
    present: number
    late: number
    checked_out: number
    on_leave: number
    missing: number
    rate: number
  }
  pending_leaves: number
  recent_punches: {
    id: number
    name: string
    check_in: string
    check_out: string | null
    is_late: boolean
    status: string
  }[]
  missing: { id: number; name: string; department: string | null }[]
  on_leave: { id: number; name: string; type: string; until: string }[]
  trend: { label: string; present: number; late: number }[]
}

export type AdminEmployee = {
  id: number
  name: string
  code: string | null
  email: string
  department: string | null
  designation: string | null
  is_active: boolean
  today?: {
    check_in: string | null
    check_out: string | null
    is_late: boolean
    status: string
  } | null
}

export type AdminLeave = {
  id: number
  employee: string
  code: string | null
  type: string
  type_label: string
  range_label: string
  days: number
  reason: string
  status: 'pending' | 'approved' | 'rejected' | 'cancelled'
  admin_notes: string | null
  approver: string | null
  applied_on: string
}

export type AdminAttendanceRow = {
  id: number
  name: string
  code: string | null
  check_in: string | null
  check_out: string | null
  worked: string | null
  status: string
  is_late: boolean
  source: string
  check_in_location: { lat: number; lng: number } | null
  check_out_location: { lat: number; lng: number } | null
}

export class ApiError extends Error {
  status: number
  errors?: Record<string, string[]>

  constructor(message: string, status: number, errors?: Record<string, string[]>) {
    super(message)
    this.status = status
    this.errors = errors
  }
}

let cachedToken: string | null = null

export async function getToken(): Promise<string | null> {
  if (cachedToken !== null) return cachedToken
  const { value } = await Preferences.get({ key: TOKEN_KEY })
  cachedToken = value
  return value
}

export async function setSession(token: string, user: User): Promise<void> {
  cachedToken = token
  await Preferences.set({ key: TOKEN_KEY, value: token })
  await Preferences.set({ key: USER_KEY, value: JSON.stringify(user) })
}

export async function getCachedUser(): Promise<User | null> {
  const { value } = await Preferences.get({ key: USER_KEY })
  if (!value) return null
  try {
    return JSON.parse(value) as User
  } catch {
    return null
  }
}

export async function clearSession(): Promise<void> {
  cachedToken = null
  await Preferences.remove({ key: TOKEN_KEY })
  await Preferences.remove({ key: USER_KEY })
}

type RequestOptions = {
  method?: 'GET' | 'POST'
  body?: unknown
  auth?: boolean
}

async function request<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const { method = 'GET', body, auth = true } = options

  const headers: Record<string, string> = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  }

  if (auth) {
    const token = await getToken()
    if (token) headers.Authorization = `Bearer ${token}`
  }

  let response: Response

  try {
    response = await fetch(`${API_BASE}${path}`, {
      method,
      headers,
      body: body ? JSON.stringify(body) : undefined,
    })
  } catch {
    // A dropped connection should read as a connection problem, not as a
    // mysterious failure from the server.
    throw new ApiError('No connection. Check your internet and try again.', 0)
  }

  const text = await response.text()
  let payload: any = null

  try {
    payload = text ? JSON.parse(text) : null
  } catch {
    throw new ApiError('The server sent an unexpected response.', response.status)
  }

  if (!response.ok) {
    if (response.status === 401) {
      await clearSession()
    }

    const firstError =
      payload?.message ??
      (payload?.errors ? (Object.values(payload.errors)[0] as string[])?.[0] : null) ??
      'Something went wrong. Please try again.'

    throw new ApiError(firstError, response.status, payload?.errors)
  }

  return payload as T
}

export const api = {
  login: (email: string, password: string) =>
    request<{ status: boolean; token: string; user: User }>('/login', {
      method: 'POST',
      body: { email, password, device_name: 'android' },
      auth: false,
    }),

  forgotPassword: (email: string) =>
    request<{ status: boolean; message: string }>('/forgot-password', {
      method: 'POST',
      body: { email },
      auth: false,
    }),

  resetWithOtp: (email: string, otp: string, password: string) =>
    request<{ status: boolean; message: string }>('/verify-reset-password', {
      method: 'POST',
      body: { email, otp, password, password_confirmation: password },
      auth: false,
    }),

  me: () => request<{ status: boolean; user: User }>('/user'),

  logout: () => request<{ status: boolean }>('/logout', { method: 'POST' }),

  home: () => request<{ status: boolean; data: HomePayload }>('/attendance/home'),

  today: () =>
    request<{
      status: boolean
      data: {
        business_date: string
        today: AttendanceRecord | null
        can_check_in: boolean
        can_check_out: boolean
      }
    }>('/attendance/today'),

  checkIn: (coords?: { latitude: number; longitude: number }) =>
    request<{ status: boolean; message: string; data: AttendanceRecord | null }>(
      '/attendance/check-in',
      { method: 'POST', body: coords ?? {} },
    ),

  checkOut: (coords?: { latitude: number; longitude: number }) =>
    request<{ status: boolean; message: string; data: AttendanceRecord | null }>(
      '/attendance/check-out',
      { method: 'POST', body: coords ?? {} },
    ),

  month: (month: string) =>
    request<{ status: boolean; data: { month: string; label: string; days: DayRow[]; summary: Summary } }>(
      `/attendance/month?month=${month}`,
    ),

  holidays: (year: number) => request<{ status: boolean; data: Holiday[] }>(`/holidays?year=${year}`),

  leaves: () =>
    request<{
      status: boolean
      data: {
        leaves: Leave[]
        types: string[]
        balance: { quota: number; used: number; remaining: number; pending: number }
      }
    }>('/leaves'),

  applyLeave: (input: { type: string; start_date: string; end_date: string; reason: string }) =>
    request<{ status: boolean; message: string; data: Leave }>('/leaves', {
      method: 'POST',
      body: input,
    }),

  cancelLeave: (id: number) =>
    request<{ status: boolean; message: string }>(`/leaves/${id}/cancel`, { method: 'POST' }),

  changePassword: (current: string, next: string) =>
    request<{ status: boolean; message: string }>('/reset-password', {
      method: 'POST',
      body: { current_password: current, password: next, password_confirmation: next },
    }),

  // ------------------------------------------------------------- admin
  adminDashboard: () => request<{ status: boolean; data: AdminDashboard }>('/admin/dashboard'),

  adminEmployees: (query?: string) =>
    request<{ status: boolean; data: AdminEmployee[] }>(
      `/admin/employees${query ? `?q=${encodeURIComponent(query)}` : ''}`,
    ),

  adminEmployeeReport: (id: number, month: string) =>
    request<{
      status: boolean
      data: {
        employee: AdminEmployee & { phone: string | null }
        month: string
        label: string
        summary: Summary
        days: DayRow[]
      }
    }>(`/admin/employees/${id}/report?month=${month}`),

  adminLeaves: (status: string) =>
    request<{
      status: boolean
      data: {
        counts: { pending: number; approved: number; rejected: number }
        leaves: AdminLeave[]
      }
    }>(`/admin/leaves?status=${status}`),

  adminDecideLeave: (id: number, decision: 'approved' | 'rejected', notes?: string) =>
    request<{ status: boolean; message: string }>(`/admin/leaves/${id}/status`, {
      method: 'POST',
      body: { status: decision, admin_notes: notes ?? null },
    }),

  adminAttendance: (date: string) =>
    request<{
      status: boolean
      data: { date: string; label: string; records: AdminAttendanceRow[] }
    }>(`/admin/attendance?date=${date}`),
}
