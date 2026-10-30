# HRMS — Android app

Employee-facing companion to the HRMS portal: punch in and out, review the
month, request leave. Built with Capacitor + React + Vite and talking to the
portal's `/api` endpoints over a Sanctum bearer token.

## Requirements

| Tool | Version used |
| --- | --- |
| Node | 24.x |
| JDK | **21** — Capacitor 7's plugins compile against a Java 21 toolchain, so 17 fails |
| Android SDK | platform 35, build-tools 35 |

The build expects `JAVA_HOME` and `ANDROID_HOME` to be set, and
`android/local.properties` to carry `sdk.dir`.

Write that path with **forward slashes** — `local.properties` is a Java
properties file, where a lone `\U` is an invalid escape that gets silently
swallowed, leaving Gradle with a mangled path:

```properties
sdk.dir=C:/Users/you/dev-tools/android-sdk
```

## Commands

```bash
npm install          # once
npm run dev          # browser preview against the live API
npm run build        # web bundle into dist/
npm run sync         # build + copy into the Android project
npm run apk          # build + sync + assemble a debug APK
```

The APK lands in `android/app/build/outputs/apk/debug/app-debug.apk`.

## Pointing at a different backend

The API base defaults to `https://hrms.iwork4sindh.com/api`. Override it at
build time:

```bash
# Android emulator talking to Laragon on the host machine
VITE_API_BASE=http://10.0.2.2/api npm run apk

# A physical phone on the same Wi-Fi as the dev machine
VITE_API_BASE=http://192.168.1.20/api npm run apk
```

Plain `http://` only works for local testing because the release manifest
does not opt into cleartext traffic for arbitrary hosts.

## Release build

A debug APK is signed with Android's shared debug key and is fine for
sideloading and testing, but not for the Play Store. For a release build,
create a keystore and sign it:

```bash
keytool -genkey -v -keystore hrms-release.keystore -alias hrms -keyalg RSA -keysize 2048 -validity 10000
```

Then add the signing config to `android/app/build.gradle` and run
`./gradlew.bat assembleRelease`. Keep the keystore out of the repository —
losing it means the app can never be updated under the same listing.

## Screens

| Screen | What it does |
| --- | --- |
| Login | Email + password, plus OTP-based password reset |
| Home | Live clock, one large punch control, month summary, recent days |
| Attendance | Any month, day by day, with hours worked vs required |
| Leave | Balance, request form, pending/approved history, cancel |
| Profile | Employment details, shift, upcoming holidays, reminders, password change |

Location is attached to a punch when the employee allows it; if they decline
or the fix times out, the punch still records.

## Attendance reminders

Each employee sets their own reminders on their own handset, under
**Profile → Attendance reminders**:

- **Check-in** — off, or 15 / 10 / 5 minutes before the shift starts, or at
  the start itself.
- **Check-out** — off, or 10 / 5 minutes before the shift ends, at the end,
  or 5 / 15 minutes after.

Either one can be switched off on its own, or both.

They are Android local notifications, so they fire without the app running
and without a server round trip. The schedule is laid down 14 days ahead and
refreshed whenever the home screen loads, so it follows shift changes.

What keeps them from becoming a nuisance:

- One notification per day per direction — never a repeating alarm.
- Weekly offs and public holidays are skipped.
- The moment someone punches, that day's matching reminder is cancelled, so
  an employee who has already checked in is not told to check in.

## Demo mode

To work on the screens without a backend or an account:

```bash
VITE_DEMO=1 npm run dev
```

A dev-server middleware then answers `/api/*` with sample data and any
email/password signs in. It lives in `vite.config.ts` under `apply: 'serve'`,
so it never reaches a production build.
