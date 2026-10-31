import { useCallback, useEffect, useState } from 'react'
import { SplashScreen } from '@capacitor/splash-screen'
import { App as CapApp } from '@capacitor/app'
import { api, clearSession, getCachedUser, getToken, type User } from './api'
import { Loader, TabIcon, Toast, icons, useToast } from './ui'
import {
  applyTheme,
  loadThemeChoice,
  watchSystemTheme,
  type ThemeChoice,
} from './theme'
import Login from './screens/Login'
import Home from './screens/Home'
import History from './screens/History'
import LeaveScreen from './screens/LeaveScreen'
import Profile from './screens/Profile'
import AdminHome from './screens/admin/AdminHome'
import AdminAttendance from './screens/admin/AdminAttendance'
import AdminLeaves from './screens/admin/AdminLeaves'

type Tab = 'home' | 'attendance' | 'leave' | 'profile'

const TABS: { key: Tab; label: string; adminLabel: string; icon: keyof typeof icons }[] = [
  { key: 'home', label: 'Home', adminLabel: 'Today', icon: 'home' },
  { key: 'attendance', label: 'Attendance', adminLabel: 'Attendance', icon: 'calendar' },
  { key: 'leave', label: 'Leave', adminLabel: 'Approvals', icon: 'leave' },
  { key: 'profile', label: 'Profile', adminLabel: 'Profile', icon: 'user' },
]

export default function App() {
  const [user, setUser] = useState<User | null>(null)
  const [booting, setBooting] = useState(true)
  const [tab, setTab] = useState<Tab>('home')
  const [theme, setTheme] = useState<ThemeChoice>('system')
  const { toast, show } = useToast()

  const isAdmin = user?.role === 'admin'

  // Theme first, so the app never flashes the wrong background.
  useEffect(() => {
    loadThemeChoice().then((choice) => {
      setTheme(choice)
      applyTheme(choice)
    })
  }, [])

  // "System" has to keep following the phone while the app is open.
  useEffect(() => {
    if (theme !== 'system') return
    return watchSystemTheme(() => applyTheme('system'))
  }, [theme])

  const changeTheme = useCallback((choice: ThemeChoice) => {
    setTheme(choice)
    applyTheme(choice)
  }, [])

  // Restore the session, then confirm the token is still good. Showing the
  // cached user first means no sign-in flash on every cold start.
  useEffect(() => {
    let cancelled = false

    async function boot() {
      try {
        const token = await getToken()

        if (!token) {
          if (!cancelled) setBooting(false)
          return
        }

        const cached = await getCachedUser()
        if (cached && !cancelled) setUser(cached)

        try {
          const fresh = await api.me()
          if (!cancelled) setUser(fresh.user)
        } catch (err: any) {
          // Only a genuine rejection from the server ends the session. A
          // flat battery of a connection used to log people out every time
          // they opened the app somewhere with no signal.
          const rejected = err?.status === 401 || err?.status === 403

          if (rejected) {
            await clearSession()
            if (!cancelled) setUser(null)
          } else if (!cached && !cancelled) {
            // Nothing cached to fall back on, so the sign-in screen it is.
            setUser(null)
          }
        }
      } finally {
        if (!cancelled) setBooting(false)
        SplashScreen.hide().catch(() => undefined)
      }
    }

    boot()

    return () => {
      cancelled = true
    }
  }, [])

  // Android back button: step back to Home before letting it close the app.
  useEffect(() => {
    const handle = CapApp.addListener('backButton', ({ canGoBack }) => {
      if (tab !== 'home') {
        setTab('home')
        return
      }
      if (!canGoBack) CapApp.exitApp()
    })

    return () => {
      handle.then((h) => h.remove()).catch(() => undefined)
    }
  }, [tab])

  async function signOut() {
    await clearSession()
    setUser(null)
    setTab('home')
  }

  if (booting) {
    return <div className="app"><Loader label="Starting up…" /></div>
  }

  if (!user) {
    return (
      <div className="app">
        <Login onSignedIn={setUser} />
        <Toast toast={toast} />
      </div>
    )
  }

  return (
    <div className="app">
      {isAdmin ? (
        <>
          {tab === 'home' && (
            <AdminHome user={user} onExpired={signOut} onOpenLeaves={() => setTab('leave')} />
          )}
          {tab === 'attendance' && <AdminAttendance onExpired={signOut} />}
          {tab === 'leave' && <AdminLeaves onToast={show} onExpired={signOut} />}
        </>
      ) : (
        <>
          {tab === 'home' && <Home user={user} onToast={show} onExpired={signOut} />}
          {tab === 'attendance' && <History onExpired={signOut} />}
          {tab === 'leave' && <LeaveScreen onToast={show} onExpired={signOut} />}
        </>
      )}

      {tab === 'profile' && (
        <Profile
          user={user}
          theme={theme}
          onThemeChange={changeTheme}
          onToast={show}
          onSignOut={signOut}
        />
      )}

      <nav className="tabs" style={{ gridTemplateColumns: `repeat(${TABS.length}, 1fr)` }}>
        {TABS.map((item) => (
          <button
            key={item.key}
            className={`tab ${tab === item.key ? 'tab--active' : ''}`}
            onClick={() => setTab(item.key)}
          >
            <TabIcon name={item.icon} />
            <span>{isAdmin ? item.adminLabel : item.label}</span>
          </button>
        ))}
      </nav>

      <Toast toast={toast} />
    </div>
  )
}
