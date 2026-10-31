import { Preferences } from '@capacitor/preferences'
import { StatusBar, Style } from '@capacitor/status-bar'

const THEME_KEY = 'hrms.theme'

export type ThemeChoice = 'system' | 'light' | 'dark'

export const THEME_CHOICES: { value: ThemeChoice; label: string; hint: string }[] = [
  { value: 'system', label: 'System', hint: 'Follow the phone setting' },
  { value: 'light', label: 'Light', hint: 'Always light' },
  { value: 'dark', label: 'Dark', hint: 'Always dark' },
]

const media = () =>
  typeof window !== 'undefined' && window.matchMedia
    ? window.matchMedia('(prefers-color-scheme: dark)')
    : null

/** What the phone itself is set to right now. */
export function systemIsDark(): boolean {
  return media()?.matches ?? true
}

export function resolveTheme(choice: ThemeChoice): 'light' | 'dark' {
  return choice === 'system' ? (systemIsDark() ? 'dark' : 'light') : choice
}

export async function loadThemeChoice(): Promise<ThemeChoice> {
  const { value } = await Preferences.get({ key: THEME_KEY })

  return value === 'light' || value === 'dark' || value === 'system' ? value : 'system'
}

export async function saveThemeChoice(choice: ThemeChoice): Promise<void> {
  await Preferences.set({ key: THEME_KEY, value: choice })
}

/**
 * Paints the chosen theme. The stylesheet keys off data-theme, and the status
 * bar and the browser chrome colour have to be told separately or the top of
 * the screen stays the wrong colour.
 */
export function applyTheme(choice: ThemeChoice): 'light' | 'dark' {
  const resolved = resolveTheme(choice)

  document.documentElement.setAttribute('data-theme', resolved)

  const chrome = resolved === 'dark' ? '#0e0b1f' : '#f4f3fb'
  document.querySelector('meta[name="theme-color"]')?.setAttribute('content', chrome)

  StatusBar.setStyle({ style: resolved === 'dark' ? Style.Dark : Style.Light }).catch(() => undefined)
  StatusBar.setBackgroundColor({ color: chrome }).catch(() => undefined)

  return resolved
}

/**
 * Keeps "System" honest: if the phone flips to dark while the app is open,
 * the app follows. Returns an unsubscribe.
 */
export function watchSystemTheme(onChange: () => void): () => void {
  const mq = media()
  if (!mq) return () => undefined

  const handler = () => onChange()

  mq.addEventListener('change', handler)
  return () => mq.removeEventListener('change', handler)
}
