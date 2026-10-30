import { useEffect, useState } from 'react'
import { SplashScreen } from '@capacitor/splash-screen'
import { StatusBar, Style } from '@capacitor/status-bar'
import { App as CapApp } from '@capacitor/app'
import { api, clearSession, getCachedUser, getToken, type User } from './api'
import { Loader, TabIcon, Toast, icons, useToast } from './ui'
import Login from './screens/Login'
import Home from './screens/Home'
import History from './screens/History'
import LeaveScreen from './screens/LeaveScreen'
import Profile from './screens/Profile'

type Tab = 'home' | 'history' | 'leave' | 'profile'

const TABS: { key: Tab; label: string; icon: keyof typeof icons }[] = [
  { key: 'home', label: 'Home', icon: 'home' },
  { key: 'history', label: 'Attendance', icon: 'calendar' },
  { key: 'leave', label: 'Leave', icon: 'leave' },
  { key: 'profile', label: 'Profile', icon: 'user' },
]

export default function App() {
  const [user, setUser] = useState<User | null>(null)
  const [booting, setBooting] = useState(true)
  const [tab, setTab] = useState<Tab>('home')
  const { toast, show } = useToast()

  // Restore the session, then confirm the token is still good. Showing the
  // cached user first means no sign-in flash on every cold start.
  useEffect(() => {
    let cancelled = false

    async function boot() {
      try {
        await StatusBar.setStyle({ style: Style.Dark }).catch(() => undefined)
        await StatusBar.setBackgroundColor({ color: '#0e0b1f' }).catch(() => undefined)

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
        } catch {
          // Expired or revoked token — fall back to the sign-in screen.
          await clearSession()
          if (!cancelled) setUser(null)
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
      {tab === 'home' && <Home user={user} onToast={show} onExpired={signOut} />}
      {tab === 'history' && <History onExpired={signOut} />}
      {tab === 'leave' && <LeaveScreen onToast={show} onExpired={signOut} />}
      {tab === 'profile' && <Profile user={user} onToast={show} onSignOut={signOut} />}

      <nav className="tabs">
        {TABS.map((item) => (
          <button
            key={item.key}
            className={`tab ${tab === item.key ? 'tab--active' : ''}`}
            onClick={() => setTab(item.key)}
          >
            <TabIcon name={item.icon} />
            <span>{item.label}</span>
          </button>
        ))}
      </nav>

      <Toast toast={toast} />
    </div>
  )
}
