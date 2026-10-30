import type { CapacitorConfig } from '@capacitor/cli'

const config: CapacitorConfig = {
  appId: 'com.iwork4sindh.hrms',
  appName: 'HRMS',
  webDir: 'dist',
  android: {
    allowMixedContent: false,
  },
  plugins: {
    SplashScreen: {
      launchShowDuration: 1200,
      backgroundColor: '#0e0b1f',
      showSpinner: false,
      androidScaleType: 'CENTER_CROP',
    },
  },
}

export default config
