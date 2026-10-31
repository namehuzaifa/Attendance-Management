# HRMS — what changed

A pass over the attendance portal: framework and runtime brought up to date,
broken modules finished, security holes closed, both dashboards rebuilt, a
mobile API added, and an Android app built on top of it.

---

## 1. Runtime and framework

| | Before | After |
| --- | --- | --- |
| PHP | 8.2.13 (local) / 8.5.12 (Apache) | **8.4.27**, matching the live server |
| Laravel | 10.48.29 | **13.39.0** |
| Sanctum | 3.3 | 4.3 |
| Breeze | 1.29 (in `require`) | 2.4 (moved to `require-dev`) |
| PHPUnit | 10.5 | 12.5 |

Laravel 13 still ships the classic `Http/Kernel`, `Console/Kernel` and
`Exceptions/Handler`, so the existing application structure was kept rather
than rewritten into the slim skeleton.

**Carbon 3 is the breaking change to know about.** Its `diffIn*` methods now
return *signed floats* where Carbon 2 returned absolute integers. Every
duration in the attendance maths was corrected, and one comparison
(`$end->diffInDays($start) > 90` in the leave module) was silently inverted
by it and is now measured forwards.

Laragon was pointed at the new PHP in `etc/apache2/fcgid.conf` and
`usr/laragon.ini`; the originals are backed up beside them as `.bak-<date>`.
This is a machine-wide switch, so other Laragon sites now run 8.4 too.

---

## 2. Things that were broken

| Problem | Effect | Fix |
| --- | --- | --- |
| `leave_requests` table had only `id` + timestamps | Leave module could not store anything; its routes were commented out | Real schema + a working module, both roles |
| `off_days` table had only `id` + timestamps | Public holidays could not be recorded, so every one was reported as an absence | Holidays module, honoured in all reports |
| `PUT /attendance/{id}` → `updateAttendance()` | Method never existed — every admin correction was a 500 | Implemented, with flags recalculated from the shift |
| `ProfileController` referenced by three routes | Class never existed; `route:list` itself crashed | Dead routes removed (the portal edits profiles via `user-edit`) |
| `UserFactory` wrote to a `name` column | No such column — every factory-made user failed | Writes `full_name`, plus `admin()` / `inactive()` states |
| Admin dashboard | Still the ecommerce demo template, showing "Your Total Earnings" and an undefined variable | Rebuilt around real HR data |
| Duplicate `user_relations` rows | Each profile save inserted another row; the shift lookup then picked one arbitrarily | De-duplicated, unique index, `updateOrCreate` |
| No unique key on `attendances` | Two rows for one person-day were possible | Unique `(user_id, date)` |
| Overnight shifts | Bare time comparison treated a 22:00–06:00 shift as negative length | Shift window anchored to a date and rolled to the next day |
| `$status .= ' | late'` | Status text grew on every write and was parsed with `str_contains` | Boolean columns `is_late`, `is_early_out`, `is_short_hour`, plus minutes |
| Hand-edited `?month=` query | Unhandled parse exception shown to the user | Validated, falls back to the current month |

---

## 3. Security

- **Privilege escalation.** `POST /user-update/{id}` took any id and applied
  the posted `user_role`, so any signed-in employee could edit a colleague —
  or make themselves an admin. Ownership is now enforced, and role, employee
  code, joining date and activation are admin-only fields.
- **`is_active` was never checked.** A deactivated employee could still sign
  in and mark attendance, on both the web and the API.
- **OTP leak.** `POST /api/forgot-password` returned the code in its own JSON
  response (`otp_for_testing`). Removed. The endpoint now answers identically
  for known and unknown addresses, so it cannot be used to discover who has an
  account, and a reset revokes existing tokens.
- **Open registration.** `POST /api/register` let anyone create an account on
  the HR system, and was broken anyway (it wrote `first_name`/`last_name` to a
  table that has `full_name`). Removed — accounts come from an administrator.
- **Deletes behind GET links.** User, shift, department, designation and IP
  deletion all happened on `GET`, so any crafted URL an admin opened would
  destroy a record. All are `POST`/`DELETE` with CSRF now.
- Login throttling on the API (`throttle:10,1`), and the last administrator
  can no longer be deleted or demoted.

---

## 4. New

**Leave** — employees apply, see their balance and cancel pending requests;
admins filter and approve or reject with a note. Overlapping requests are
refused, and weekly offs and holidays inside a range are not deducted.

**Holidays** — an admin calendar. A holiday is never counted as an absence,
and approved leave shows as leave rather than absence.

**Reports** — department filter, an attendance-correction dialog, and a CSV
export of the all-employees month for payroll.

**Dashboards** — the employee gets a live punch card, month gauge, recent
activity, the coming week, leave balance and upcoming holidays. The admin
gets today's turnout, a 14-day trend chart, department breakdown, pending
approvals, who has not checked in, and who is on leave.

**Mobile API** (`routes/api.php`, Sanctum bearer tokens):

```
POST /api/login                 POST /api/logout            GET  /api/user
POST /api/forgot-password       POST /api/verify-reset-password
POST /api/reset-password        POST /api/profile
GET  /api/attendance/home       GET  /api/attendance/today
GET  /api/attendance/month      POST /api/attendance/check-in
POST /api/attendance/check-out  GET  /api/holidays
GET  /api/leaves                POST /api/leaves            POST /api/leaves/{id}/cancel

# administrators only (api.admin middleware; employees get a 403)
GET  /api/admin/dashboard       GET  /api/admin/employees
GET  /api/admin/employees/{id}/report
GET  /api/admin/attendance      GET  /api/admin/leaves
POST /api/admin/leaves/{id}/status
```

Punches record latitude, longitude, IP and source (`web` / `mobile`).

Tokens last 60 days and slide forward on use (`RefreshApiToken`), so an
employee who opens the app stays signed in.

**Punch locations** — check-in and check-out already recorded latitude and
longitude; now an administrator can see them. A 📍 button on each attendance
row opens the two points on a map, with the straight-line distance between
them and a warning past 500 m. It uses Google Maps' keyless endpoints
(`output=embed` to show, `?api=1` to hand off), so there is no API key and
nothing to bill. Available in the portal and in the app.

**One brand mark** — the portal's logo was a 270×250 stock image with the
watermark still on it. Replaced with an SVG mark (an attendance ring around
the person figure from the original), used for the portal logo, the favicon,
the app's launcher icon, its splash and its notification icon — one file that
stays sharp from 16px to 1024px.

**Android app** — employee and administrator flows, light/dark/system theming,
and sessions that survive a lost connection. See
[mobile/README.md](mobile/README.md).

---

## 5. Tests

52 tests / 200 assertions, against a real MySQL schema (the integrity
migration uses a multi-table `DELETE` that SQLite cannot parse).

```bash
php artisan test
```

Create the test database once:

```sql
CREATE DATABASE `attendance_management_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

`PortalSmokeTest` opens every screen as both roles, `MobileApiTest` pins the
JSON shape the Android app depends on, and `ProfileTest` covers profile and
administrator-safety rules.

---

## 6. Deploying to the live server

The live host already runs PHP 8.4.27, so Laravel 13 is supported there.

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link      # avatars uploaded from the app
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Take a database backup first: the integrity migration **deletes** duplicate
`user_relations` and `attendances` rows, keeping the newest of each.
