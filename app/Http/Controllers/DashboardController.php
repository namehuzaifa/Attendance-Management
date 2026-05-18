<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    function index() {

        if (Auth::user()->user_role == 'user') {

            $user = Auth::user();
            $userRelation = $user?->relation;
            $shift = $userRelation?->shiftTiming;
            $shiftStart = Carbon::parse($shift?->start_time);
            $shiftEnd   = Carbon::parse($shift?->end_time);
            $now = Carbon::now();
            $today = Carbon::today();

            if ($shiftEnd->lessThan($shiftStart) ) {
                $shiftEnd->addDay();
                $check = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();
                if (!$check?->check_in && !$check?->check_out) {
                    $today = $today->subDay();
                }
            }

            $checkInAvailable = $now->between($shiftStart->subHours(2), $shiftEnd);
            $checkOutAvailable =  $now->lessThanOrEqualTo($shiftEnd->addHours(4));
            $attendanceToday = Attendance::where('user_id', $user->id)->whereDate('date', $checkInAvailable ?  Carbon::today() : $today)->first();

            // ========= Shift Information: Next 7 days =========
            $offDays = ($shift && is_array($shift->off_days)) ? $shift->off_days : ['Sunday', 'Saturday'];
            $nextWeek = [];
            for ($i = 0; $i < 7; $i++) {
                $date = Carbon::today()->addDays($i);
                $dayName = $date->format('l');
                $isOff = in_array($dayName, $offDays);
                $nextWeek[] = [
                    'date' => $date->format('D, d M'),
                    'day' => $dayName,
                    'start_time' => $isOff ? null : ($shift ? Carbon::parse($shift->start_time)->format('h:i A') : null),
                    'end_time' => $isOff ? null : ($shift ? Carbon::parse($shift->end_time)->format('h:i A') : null),
                    'is_off' => $isOff,
                ];
            }

            // ========= In Out Timing: Last 7 days including today =========
            $last7Start = Carbon::now()->subDays(6)->startOfDay();
            $last7End = Carbon::now()->endOfDay();
            $weekAttendances = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$last7Start->format('Y-m-d'), $last7End->format('Y-m-d')])
                ->get()
                ->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d'));

            $weekReport = [];
            foreach (CarbonPeriod::create($last7Start, $last7End) as $date) {
                if ($date->gt(now())) break;
                $dayName = $date->format('l');
                $dateKey = $date->format('Y-m-d');
                $isOff = in_array($dayName, $offDays);

                if ($isOff) {
                    $weekReport[] = [
                        'date' => $date->format('D, d M'),
                        'status' => 'Off Day',
                        'check_in' => null,
                        'check_out' => null,
                        'worked_hours' => null,
                    ];
                } elseif (isset($weekAttendances[$dateKey])) {
                    $rec = $weekAttendances[$dateKey];
                    $workedMins = null;
                    if ($rec->check_in && $rec->check_out) {
                        $workedMins = Carbon::parse($rec->check_in)->diffInMinutes(Carbon::parse($rec->check_out));
                    }
                    $weekReport[] = [
                        'date' => $date->format('D, d M'),
                        'status' => $rec->status ?? 'on time',
                        'check_in' => $rec->check_in ? Carbon::parse($rec->check_in)->format('h:i A') : '-',
                        'check_out' => $rec->check_out ? Carbon::parse($rec->check_out)->format('h:i A') : '-',
                        'worked_hours' => $workedMins !== null ? floor($workedMins / 60) . 'h ' . ($workedMins % 60) . 'm' : '-',
                    ];
                } else {
                    $weekReport[] = [
                        'date' => $date->format('D, d M'),
                        'status' => 'Absent',
                        'check_in' => '-',
                        'check_out' => '-',
                        'worked_hours' => '-',
                    ];
                }
            }

            // ========= Employee Stats: Current month =========
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();
            $monthAttendances = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                ->get()
                ->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d'));

            $totalWorkingDays = $presentDays = $absentDays = $lateDays = $earlyOutDays = $shortHourDays = $offDaysCount = 0;

            foreach (CarbonPeriod::create($monthStart, min($monthEnd, now())) as $date) {
                $dayName = $date->format('l');
                $dateKey = $date->format('Y-m-d');
                $isOff = in_array($dayName, $offDays);

                if ($isOff) {
                    $offDaysCount++;
                    continue;
                }

                $totalWorkingDays++;

                if (isset($monthAttendances[$dateKey])) {
                    $rec = $monthAttendances[$dateKey];
                    $presentDays++;
                    if (str_contains($rec->status ?? '', 'late')) $lateDays++;
                    if (str_contains($rec->status ?? '', 'early out')) $earlyOutDays++;
                    if (str_contains($rec->status ?? '', 'short hour')) $shortHourDays++;
                } else {
                    $absentDays++;
                }
            }

            $employeeStats = [
                'total_working_days' => $totalWorkingDays,
                'present' => $presentDays,
                'off_days' => $offDaysCount,
                'absent' => $absentDays,
                'late' => $lateDays,
                'early_out' => $earlyOutDays,
                'short_hour' => $shortHourDays,
            ];

            return view('modules.admin.dashboard.index', compact(
                'checkInAvailable', 'checkOutAvailable', 'attendanceToday',
                'nextWeek', 'weekReport', 'employeeStats'
            ));

        } else if (Auth::user()->user_role == 'admin') {
            return view('modules.admin.dashboard.index');
        }
    }
}
