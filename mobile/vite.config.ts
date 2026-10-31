import { defineConfig, type Plugin } from 'vite'
import react from '@vitejs/plugin-react'

/**
 * Dev-only stand-in for the portal API, so the screens can be designed and
 * reviewed without a backend or a real account. `apply: 'serve'` keeps it on
 * the dev server — none of this reaches a production build.
 *
 * Run it with: VITE_DEMO=1 VITE_API_BASE=/api npm run dev
 */
function demoApi(): Plugin {
  const pad = (n: number) => String(n).padStart(2, '0')
  const today = new Date()
  const iso = (d: Date) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`

  const user = {
    id: 7,
    full_name: 'Ayesha Siddiqui',
    employee_code: 'EMP-0042',
    email: 'ayesha@iwork4sindh.com',
    phone: '0300 1234567',
    role: 'user',
    avatar_url: '',
    joining_date: '2024-03-11',
    is_active: true,
    department: 'Engineering',
    designation: 'Senior Developer',
    manager: 'Bilal Ahmed',
    shift: {
      name: 'Day shift',
      start_time: '09:00',
      end_time: '17:00',
      grace_period: 15,
      off_days: ['Saturday', 'Sunday'],
    },
  }

  const day = (offset: number, status: string, tone: string, inAt?: string, outAt?: string) => {
    const d = new Date(today)
    d.setDate(today.getDate() - offset)

    return {
      date: iso(d),
      label: d.toLocaleDateString('en-GB', { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' }),
      day: d.toLocaleDateString('en-US', { weekday: 'long' }),
      is_today: offset === 0,
      status,
      tone,
      check_in: inAt ?? null,
      check_out: outAt ?? null,
      worked: inAt && outAt ? '8h 12m' : null,
      late: status === 'Late',
      early_out: false,
      short_hour: false,
    }
  }

  const recent = [
    day(0, 'Present', 'success', '09:04 AM', '05:16 PM'),
    day(1, 'Late', 'warning', '09:48 AM', '06:02 PM'),
    day(2, 'Present', 'success', '08:57 AM', '05:09 PM'),
    day(3, 'Weekly Off', 'secondary'),
    day(4, 'Weekly Off', 'secondary'),
    day(5, 'Sick Leave', 'primary'),
    day(6, 'Present', 'success', '09:01 AM', '05:22 PM'),
  ]

  const summary = {
    working_days: 21, present: 18, absent: 1, late: 3, early_out: 1,
    short_hour: 1, leave: 2, holiday: 1, weekly_off: 8,
    worked_minutes: 8820, required_minutes: 10080, attendance_pct: 86,
  }

  // Sign in with an email containing "admin" to preview the admin side.
  const adminUser = { ...user, id: 1, full_name: 'Bilal Ahmed', email: 'admin@iwork4sindh.com', role: 'admin' }

  // Which of the two the demo is currently signed in as.
  let current: typeof user = user

  const trend = Array.from({ length: 14 }, (_, i) => {
    const d = new Date(today)
    d.setDate(today.getDate() - (13 - i))
    return { label: d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' }), present: 9 + ((i * 5) % 7), late: i % 4 }
  })

  const routes: Record<string, unknown> = {
    'POST /api/login': { status: true, token: 'demo-token', user },
    'GET /api/user': { status: true, user },
    'POST /api/logout': { status: true },

    'GET /api/admin/dashboard': {
      status: true,
      data: {
        date: iso(today),
        label: today.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }),
        holiday: null,
        stats: { employees: 18, present: 14, late: 3, checked_out: 6, on_leave: 2, missing: 2, rate: 78 },
        pending_leaves: 3,
        recent_punches: [
          { id: 1, name: 'Ayesha Siddiqui', check_in: '09:04 AM', check_out: null, is_late: false, status: 'On time' },
          { id: 2, name: 'Hamza Qureshi', check_in: '09:48 AM', check_out: null, is_late: true, status: 'Late' },
          { id: 3, name: 'Sana Malik', check_in: '08:57 AM', check_out: '05:09 PM', is_late: false, status: 'On time' },
        ],
        missing: [
          { id: 4, name: 'Imran Shah', department: 'App' },
          { id: 5, name: 'Nadia Khan', department: 'Web' },
        ],
        on_leave: [
          { id: 6, name: 'Fahad Iqbal', type: 'Sick', until: '02 Nov' },
          { id: 7, name: 'Zoya Raza', type: 'Annual', until: '05 Nov' },
        ],
        trend,
      },
    },

    'GET /api/admin/employees': {
      status: true,
      data: [
        { id: 7, name: 'Ayesha Siddiqui', code: 'EMP-0042', email: 'ayesha@iwork4sindh.com', department: 'Engineering', designation: 'Senior Developer', is_active: true, today: { check_in: '09:04 AM', check_out: null, is_late: false, status: 'On time' } },
        { id: 8, name: 'Hamza Qureshi', code: 'EMP-0043', email: 'hamza@iwork4sindh.com', department: 'App', designation: 'Developer', is_active: true, today: { check_in: '09:48 AM', check_out: null, is_late: true, status: 'Late' } },
      ],
    },

    'GET /api/admin/attendance': {
      status: true,
      data: {
        date: iso(today),
        label: today.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }),
        records: [
          {
            id: 1, name: 'Ayesha Siddiqui', code: 'EMP-0042',
            check_in: '09:04 AM', check_out: '05:16 PM', worked: '8h 12m',
            status: 'On time', is_late: false, source: 'mobile',
            check_in_location: { lat: 24.8607343, lng: 67.0011364 },
            check_out_location: { lat: 24.8611, lng: 67.0015 },
          },
          {
            id: 2, name: 'Hamza Qureshi', code: 'EMP-0043',
            check_in: '09:48 AM', check_out: null, worked: null,
            status: 'Late', is_late: true, source: 'web',
            check_in_location: null, check_out_location: null,
          },
        ],
      },
    },

    'GET /api/admin/leaves': {
      status: true,
      data: {
        counts: { pending: 3, approved: 12, rejected: 2 },
        leaves: [
          {
            id: 1, employee: 'Ayesha Siddiqui', code: 'EMP-0042', type: 'annual', type_label: 'Annual',
            range_label: '10 Nov – 11 Nov 2026', days: 2, reason: 'Family wedding out of town',
            status: 'pending', admin_notes: null, approver: null, applied_on: '28 Oct 2026',
          },
          {
            id: 2, employee: 'Hamza Qureshi', code: 'EMP-0043', type: 'sick', type_label: 'Sick',
            range_label: '03 Nov – 03 Nov 2026', days: 1, reason: 'Doctor appointment',
            status: 'pending', admin_notes: null, approver: null, applied_on: '29 Oct 2026',
          },
        ],
      },
    },

    'GET /api/attendance/home': {
      status: true,
      data: {
        server_time: today.toISOString(),
        business_date: iso(today),
        today: {
          id: 1, date: iso(today),
          check_in: today.toISOString(), check_out: null,
          check_in_label: '09:04 AM', check_out_label: null,
          status: 'On time', is_late: false, is_early_out: false, is_short_hour: false,
          late_minutes: null, worked_minutes: null, worked_label: null, source: 'mobile',
        },
        shift: {
          start: today.toISOString(), end: today.toISOString(),
          start_label: '09:00 AM', end_label: '05:00 PM',
          grace_period: 15, total_minutes: 480,
        },
        can_check_in: false,
        can_check_out: true,
        is_off_day: false,
        holiday: null,
        ip_restricted: false,
        summary,
        recent,
        leave_balance: { quota: 20, used: 6, remaining: 14 },
        pending_leaves: 1,
      },
    },

    'GET /api/attendance/today': {
      status: true,
      data: { business_date: iso(today), today: null, can_check_in: false, can_check_out: true },
    },

    'GET /api/attendance/month': {
      status: true,
      data: {
        month: `${today.getFullYear()}-${pad(today.getMonth() + 1)}`,
        label: today.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' }),
        days: recent,
        summary,
      },
    },

    'GET /api/holidays': {
      status: true,
      data: [
        { id: 1, title: 'Quaid-e-Azam Day', date: '2026-12-25', label: 'Fri, 25 Dec 2026', description: null, is_past: false },
        { id: 2, title: 'Kashmir Day', date: '2027-02-05', label: 'Fri, 05 Feb 2027', description: null, is_past: false },
      ],
    },

    'GET /api/leaves': {
      status: true,
      data: {
        types: ['sick', 'casual', 'annual', 'emergency', 'unpaid'],
        balance: { quota: 20, used: 6, remaining: 14, pending: 2 },
        leaves: [
          {
            id: 1, type: 'annual', type_label: 'Annual',
            start_date: '2026-11-10', end_date: '2026-11-11',
            range_label: '10 Nov – 11 Nov 2026', days: 2,
            reason: 'Family wedding out of town', status: 'pending',
            admin_notes: null, approver: null, acted_at: null, created_at: today.toISOString(),
          },
          {
            id: 2, type: 'sick', type_label: 'Sick',
            start_date: '2026-10-21', end_date: '2026-10-21',
            range_label: '21 Oct – 21 Oct 2026', days: 1,
            reason: 'Fever', status: 'approved',
            admin_notes: 'Get well soon', approver: 'Bilal Ahmed',
            acted_at: today.toISOString(), created_at: today.toISOString(),
          },
          {
            id: 3, type: 'casual', type_label: 'Casual',
            start_date: '2026-09-14', end_date: '2026-09-15',
            range_label: '14 Sep – 15 Sep 2026', days: 2,
            reason: 'Personal errand', status: 'rejected',
            admin_notes: 'Release week — please re-plan', approver: 'Bilal Ahmed',
            acted_at: today.toISOString(), created_at: today.toISOString(),
          },
        ],
      },
    },
  }

  return {
    name: 'hrms-demo-api',
    apply: 'serve',

    // One flag is enough: demo mode also points the client at these routes.
    config() {
      if (!process.env.VITE_DEMO) return

      return { define: { 'import.meta.env.VITE_API_BASE': JSON.stringify('/api') } }
    },

    configureServer(server) {
      if (!process.env.VITE_DEMO) return

      server.middlewares.use((req, res, next) => {
        const url = (req.url ?? '').split('?')[0]
        if (!url.startsWith('/api/')) return next()

        const send = (body: unknown) => {
          res.setHeader('Content-Type', 'application/json')
          res.end(JSON.stringify(body))
        }

        // Signing in with an address containing "admin" previews the admin
        // side; the choice sticks so /api/user agrees after a reload.
        if (req.method === 'POST' && url === '/api/login') {
          let raw = ''
          req.on('data', (chunk) => { raw += chunk })
          req.on('end', () => {
            current = /admin/i.test(raw) ? adminUser : user
            send({ status: true, token: 'demo-token', user: current })
          })
          return
        }

        if (url === '/api/user') {
          send({ status: true, user: current })
          return
        }

        send(routes[`${req.method} ${url}`] ?? { status: true, message: 'Done (demo)', data: null })
      })
    },
  }
}

export default defineConfig({
  plugins: [react(), demoApi()],
  build: { outDir: 'dist', chunkSizeWarningLimit: 1200 },
})
