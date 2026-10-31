import { useState, type FormEvent } from 'react'
import { api, setSession, type User } from '../api'
import { Alert } from '../ui'
import Logo from '../Logo'

type Mode = 'login' | 'forgot' | 'reset'

export default function Login({ onSignedIn }: { onSignedIn: (user: User) => void }) {
  const [mode, setMode] = useState<Mode>('login')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [otp, setOtp] = useState('')
  const [newPassword, setNewPassword] = useState('')
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [notice, setNotice] = useState<string | null>(null)

  async function submit(event: FormEvent) {
    event.preventDefault()
    setBusy(true)
    setError(null)
    setNotice(null)

    try {
      if (mode === 'login') {
        const result = await api.login(email.trim(), password)
        await setSession(result.token, result.user)
        onSignedIn(result.user)
        return
      }

      if (mode === 'forgot') {
        const result = await api.forgotPassword(email.trim())
        setNotice(result.message)
        setMode('reset')
        return
      }

      const result = await api.resetWithOtp(email.trim(), otp.trim(), newPassword)
      setNotice(result.message)
      setMode('login')
      setPassword('')
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Something went wrong.')
    } finally {
      setBusy(false)
    }
  }

  return (
    <form className="login" onSubmit={submit}>
      <Logo size={76} className="login__mark" />

      <h1 className="login__title">
        {mode === 'login' ? 'Welcome back' : mode === 'forgot' ? 'Reset password' : 'Enter your code'}
      </h1>
      <p className="login__sub">
        {mode === 'login'
          ? 'Sign in to mark your attendance.'
          : mode === 'forgot'
            ? 'We will email you a one-time code.'
            : 'Check your inbox for the 6-digit code.'}
      </p>

      {error && <Alert tone="error">{error}</Alert>}
      {notice && <Alert tone="success">{notice}</Alert>}

      <div className="field">
        <label htmlFor="email">Email</label>
        <input
          id="email"
          type="email"
          inputMode="email"
          autoCapitalize="none"
          autoCorrect="off"
          placeholder="you@company.com"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
        />
      </div>

      {mode === 'login' && (
        <div className="field">
          <label htmlFor="password">Password</label>
          <input
            id="password"
            type="password"
            placeholder="••••••••"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
        </div>
      )}

      {mode === 'reset' && (
        <>
          <div className="field">
            <label htmlFor="otp">One-time code</label>
            <input
              id="otp"
              inputMode="numeric"
              placeholder="123456"
              value={otp}
              onChange={(e) => setOtp(e.target.value)}
              required
            />
          </div>
          <div className="field">
            <label htmlFor="newPassword">New password</label>
            <input
              id="newPassword"
              type="password"
              placeholder="At least 8 characters"
              value={newPassword}
              onChange={(e) => setNewPassword(e.target.value)}
              required
            />
          </div>
        </>
      )}

      <button className="btn btn--primary" type="submit" disabled={busy} style={{ marginTop: 8 }}>
        {busy && <span className="spinner" />}
        {mode === 'login' ? 'Sign in' : mode === 'forgot' ? 'Send code' : 'Reset password'}
      </button>

      <div style={{ textAlign: 'center', marginTop: 18 }}>
        {mode === 'login' ? (
          <button type="button" className="link" onClick={() => { setMode('forgot'); setError(null) }}>
            Forgot your password?
          </button>
        ) : (
          <button type="button" className="link" onClick={() => { setMode('login'); setError(null) }}>
            Back to sign in
          </button>
        )}
      </div>

      <p className="field__hint" style={{ textAlign: 'center', marginTop: 26 }}>
        Accounts are created by your administrator.
      </p>
    </form>
  )
}
