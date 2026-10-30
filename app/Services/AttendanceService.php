<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\IpAddress;
use App\Models\LeaveRequest;
use App\Models\OffDay;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * Single source of truth for punching in/out and for reading a day back.
 *
 * The rules used to live inline in AttendanceController, DashboardController
 * and the report actions, each with its own slightly different version of
 * "is this person late", which is why the three screens disagreed.
 */
class AttendanceService
{
    /**
     * Shift start/end anchored on a concrete date. An end that is not after
     * the start means the shift runs past midnight, so it lands on the next
     * day — the old code compared bare times and got this backwards.
     *
     * @return array{start: Carbon, end: Carbon, minutes: int, grace: int}|null
     */
    public function shiftWindow(User $user, Carbon $date): ?array
    {
        $shift = $user->shift();

        if (!$shift || !$shift->start_time || !$shift->end_time) {
            return null;
        }

        $start = $date->copy()->setTimeFrom(Carbon::parse($shift->start_time));
        $end   = $date->copy()->setTimeFrom(Carbon::parse($shift->end_time));

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return [
            'start'   => $start,
            'end'     => $end,
            // Carbon 3 returns a signed float here, so pin it to whole minutes.
            'minutes' => (int) $start->diffInMinutes($end),
            'grace'   => (int) ($shift->grace_period ?? 0),
        ];
    }

    public function ipRestrictionEnabled(): bool
    {
        return optional(Setting::where('key', 'ip_restriction_status')->first())->value === 'on';
    }

    /**
     * Office-network check. Admins are exempt so they can never lock
     * themselves out of the portal while configuring it.
     */
    public function ipAllowed(User $user, ?string $ip): bool
    {
        if ($user->isAdmin() || !$this->ipRestrictionEnabled()) {
            return true;
        }

        return IpAddress::where('ip_address', $ip)->exists();
    }

    /**
     * The business date a punch belongs to. For an overnight shift a punch
     * made after midnight still counts towards the day the shift started.
     */
    public function businessDate(User $user, ?Carbon $at = null): Carbon
    {
        $at    = $at ? $at->copy() : now();
        $today = $at->copy()->startOfDay();

        $yesterday = $this->shiftWindow($user, $today->copy()->subDay());

        if ($yesterday && !$yesterday['end']->isSameDay($yesterday['start'])) {
            if ($at->between($yesterday['start'], $yesterday['end']->copy()->addHours(4))) {
                return $today->copy()->subDay();
            }
        }

        return $today;
    }

    /**
     * @return array{ok: bool, message: string, attendance?: Attendance}
     */
    public function checkIn(User $user, array $meta = []): array
    {
        if (!$this->ipAllowed($user, $meta['ip'] ?? null)) {
            return ['ok' => false, 'message' => 'You are not connected to the office network.'];
        }

        $now  = now();
        $date = $this->businessDate($user, $now);

        $existing = Attendance::where('user_id', $user->id)->whereDate('date', $date)->first();

        if ($existing && $existing->check_in) {
            return ['ok' => false, 'message' => 'You have already checked in today.'];
        }

        $window = $this->shiftWindow($user, $date);
        $late   = 0;

        if ($window) {
            $threshold = $window['start']->copy()->addMinutes($window['grace']);
            $late      = $now->greaterThan($threshold) ? (int) $threshold->diffInMinutes($now) : 0;
        }

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => $date->toDateString()],
            [
                'check_in'     => $now,
                'status'       => $late > 0 ? 'late' : 'on time',
                'is_late'      => $late > 0,
                'late_minutes' => $late > 0 ? $late : null,
                'check_in_ip'  => $meta['ip'] ?? null,
                'check_in_lat' => $meta['lat'] ?? null,
                'check_in_lng' => $meta['lng'] ?? null,
                'source'       => $meta['source'] ?? 'web',
            ]
        );

        return [
            'ok' => true,
            'message' => $late > 0
                ? 'Checked in — you are ' . $this->humanMinutes($late) . ' late.'
                : 'Checked in successfully. Have a great day!',
            'attendance' => $attendance,
        ];
    }

    /**
     * @return array{ok: bool, message: string, attendance?: Attendance}
     */
    public function checkOut(User $user, array $meta = []): array
    {
        if (!$this->ipAllowed($user, $meta['ip'] ?? null)) {
            return ['ok' => false, 'message' => 'You are not connected to the office network.'];
        }

        $now  = now();
        $date = $this->businessDate($user, $now);

        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $date)->first();

        if (!$attendance || !$attendance->check_in) {
            return ['ok' => false, 'message' => 'You must check in before checking out.'];
        }

        if ($attendance->check_out) {
            return ['ok' => false, 'message' => 'You have already checked out.'];
        }

        $checkIn = Carbon::parse($attendance->check_in);
        $worked  = (int) $checkIn->diffInMinutes($now);
        $window  = $this->shiftWindow($user, $date);

        $earlyOut = $shortHour = false;

        if ($window) {
            $earlyOut  = $now->lessThan($window['end']);
            $shortHour = $worked < ($window['minutes'] - $window['grace']);
        }

        $attendance->update([
            'check_out'      => $now,
            'worked_minutes' => $worked,
            'is_early_out'   => $earlyOut,
            'is_short_hour'  => $shortHour,
            'check_out_ip'   => $meta['ip'] ?? null,
            'check_out_lat'  => $meta['lat'] ?? null,
            'check_out_lng'  => $meta['lng'] ?? null,
        ]);

        return [
            'ok'         => true,
            'message'    => 'Checked out — ' . $this->humanMinutes($worked) . ' worked today.',
            'attendance' => $attendance->fresh(),
        ];
    }

    /**
     * Day-by-day picture of a month, resolving weekly offs, public holidays,
     * approved leave and attendance into one status per date.
     *
     * @return array{days: array, summary: array}
     */
    public function monthlyReport(User $user, string $month): array
    {
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = Carbon::parse($month . '-01')->endOfMonth();

        $records = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(fn ($a) => Carbon::parse($a->date)->format('Y-m-d'));

        $holidays = OffDay::whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(fn ($h) => Carbon::parse($h->date)->format('Y-m-d'));

        $leaves = LeaveRequest::where('user_id', $user->id)
            ->approved()
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->get();

        $weeklyOff = $user->weeklyOffDays();
        $window    = $this->shiftWindow($user, $start);
        $required  = $window['minutes'] ?? null;

        $days = [];
        $summary = [
            'working_days' => 0, 'present' => 0, 'absent' => 0, 'late' => 0,
            'early_out' => 0, 'short_hour' => 0, 'leave' => 0, 'holiday' => 0,
            'weekly_off' => 0, 'worked_minutes' => 0, 'required_minutes' => 0,
        ];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            if ($date->gt(now())) {
                break;
            }

            $key = $date->format('Y-m-d');
            $row = [
                'date'       => $key,
                'label'      => $date->format('D, d M Y'),
                'day'        => $date->format('l'),
                'is_today'   => $date->isToday(),
                'check_in'   => null,
                'check_out'  => null,
                'worked'     => null,
                'late'       => false,
                'early_out'  => false,
                'short_hour' => false,
            ];

            $record = $records[$key] ?? null;

            // A punch always wins: someone who worked a holiday is present.
            if ($record && $record->check_in) {
                $summary['present']++;

                if (!in_array($date->format('l'), $weeklyOff) && !isset($holidays[$key])) {
                    $summary['working_days']++;
                    if ($required) {
                        $summary['required_minutes'] += $required;
                    }
                }

                if ($record->is_late)       $summary['late']++;
                if ($record->is_early_out)  $summary['early_out']++;
                if ($record->is_short_hour) $summary['short_hour']++;

                $summary['worked_minutes'] += (int) $record->worked_minutes;

                $days[] = array_merge($row, [
                    'status'     => $record->is_late ? 'Late' : 'Present',
                    'tone'       => $record->is_late ? 'warning' : 'success',
                    'check_in'   => Carbon::parse($record->check_in)->format('h:i A'),
                    'check_out'  => $record->check_out ? Carbon::parse($record->check_out)->format('h:i A') : null,
                    'worked'     => $record->worked_hours,
                    'late'       => (bool) $record->is_late,
                    'early_out'  => (bool) $record->is_early_out,
                    'short_hour' => (bool) $record->is_short_hour,
                ]);

                continue;
            }

            if (isset($holidays[$key])) {
                $summary['holiday']++;
                $days[] = array_merge($row, ['status' => $holidays[$key]->title, 'tone' => 'info']);
                continue;
            }

            if (in_array($date->format('l'), $weeklyOff)) {
                $summary['weekly_off']++;
                $days[] = array_merge($row, ['status' => 'Weekly Off', 'tone' => 'secondary']);
                continue;
            }

            $summary['working_days']++;
            if ($required) {
                $summary['required_minutes'] += $required;
            }

            $leave = $leaves->first(fn ($l) => $date->betweenIncluded($l->start_date, $l->end_date));

            if ($leave) {
                $summary['leave']++;
                $days[] = array_merge($row, ['status' => ucfirst($leave->type) . ' Leave', 'tone' => 'primary']);
                continue;
            }

            $summary['absent']++;
            $days[] = array_merge($row, ['status' => 'Absent', 'tone' => 'danger']);
        }

        $summary['attendance_pct'] = $summary['working_days'] > 0
            ? (int) round(($summary['present'] / $summary['working_days']) * 100)
            : 0;

        return ['days' => $days, 'summary' => $summary];
    }

    public function humanMinutes(?int $minutes): string
    {
        if ($minutes === null) {
            return '—';
        }

        return intdiv($minutes, 60) . 'h ' . ($minutes % 60) . 'm';
    }
}
