<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\CarbonPeriod;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $month = $request->get('month', now()->format('Y-m'));

        // $shift = $user->shiftTiming; // Relation banani hogi User → ShiftTiming
        // $offDays = $shift && $shift->off_days ? json_decode($shift->off_days, true) : [];
        $offDays = ['Sunday', 'Saturday'];

        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth   = Carbon::parse($month . '-01')->endOfMonth();

        $attendances = $user->attendances()
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->date)->format('Y-m-d'));

        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);
        $report = [];

        $i = 1;
        foreach ($period as $date) {
            $dayName = $date->format('l');
            $dateKey = $date->format('Y-m-d');

            if (isset($attendances[$dateKey])) {
                // User ne us din attendance di
                $report[] = [
                    'id' => $i,
                    'date' => $date->format('D d-M-Y'),
                    'status' => $attendances[$dateKey]?->status,
                    'check_in' => $attendances[$dateKey]?->check_in ? Carbon::parse($attendances[$dateKey]?->check_in)?->format('D h:i:s a') : null,
                    'check_out' => $attendances[$dateKey]?->check_out ? Carbon::parse($attendances[$dateKey]?->check_out)?->format('D h:i:s a') : null,
                ];
            } else {
                // Attendance nahi di
                if (in_array($dayName, $offDays)) {
                    $report[] = [
                        'id' => $i,
                        'date' => $date->format('D d-M-Y'),
                        'status' => 'Off Day',
                        'check_in' => null,
                        'check_out' => null,
                    ];
                } else {
                    $report[] = [
                        'id' => $i,
                        'date' => $date->format('D d-M-Y'),
                        'status' => 'Absent',
                        'check_in' => null,
                        'check_out' => null,
                    ];
                }
            }

            $i++;
            if (now()->format('Y-m-d') == $date->format('Y-m-d')) {
               break;
            }
        }

        $attendances = json_decode(json_encode($report));
        return view('modules.admin.attendance.list', compact('attendances', 'month'));
    }

    public function indexOld(Request $request)
    {
        $user = Auth::user();
        $month = $request->get('month', now()->format('Y-m'));

        $attendances = $user->attendances()
            ->whereYear('date', substr($month, 0, 4))
            ->whereMonth('date', substr($month, 5, 2))
            ->orderBy('date', 'desc')
            ->paginate(31);

        return view('modules.admin.attendance.list', compact('attendances', 'month'));
    }

    public function checkIn(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        // Check if already checked in
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance && $attendance->check_in) {
            return redirect()->back()->with([
                'message' => 'Already checked in today',
                'status' => 'failed'
            ]);
        }

        $checkInTime = now();

        // Create or update attendance
        $attendance = Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
                'check_in' => $checkInTime,
                'status' => $this->determineStatus($user, $checkInTime)
            ]
        );

        return redirect()->back()->with([
            'message' => 'Checked in successfully',
            'status' => 'success'
        ]);

    }

    public function checkOut(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            $yesterday = now()->subDay()->toDateString();
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $yesterday)
                ->first();
        }

        if (!$attendance || !$attendance->check_in) {
            // return response()->json(['error' => 'Must check in first'], 400);
            return redirect()->back()->with([
                'message' => 'You must check in first before checking out.',
                'status' => 'failed'
            ]);
        }

        if ($attendance->check_out) {
            // return response()->json(['error' => 'Already checked out'], 400);
            return redirect()->back()->with([
                'message' => 'Already checked out',
                'status' => 'failed'
            ]);
        }

            // 🟢 Step 5: Check-out time set karo
        // $checkOutTime = now();
        // $attendance->check_out = $checkOutTime->format('H:i:s');

        // // 🟢 Step 6: Status update karo based on early out / short hour
        // $shift = $user->relation->shiftTiming;
        // $startTime = Carbon::parse($shift->start_time);
        // $endTime = Carbon::parse($shift->end_time);

        // // Agar shift end next day me hai (e.g. 01:00 AM), to end time ko +1 day karo
        // if ($endTime->lessThan($startTime)) {
        //     $endTime->addDay();
        // }

        // $checkIn = Carbon::parse($attendance->check_in);
        // if ($checkIn->greaterThan($endTime)) {
        //     $checkIn->subDay(); // midnight shift ke liye adjust
        // }

        // $checkOut = $checkOutTime;

        // $totalShiftMinutes = $startTime->diffInMinutes($endTime);
        // $workedMinutes = $checkIn->diffInMinutes($checkOut);

        // $newStatus = $attendance->status;

        // // ⏰ Early out
        // if ($checkOut->lt($endTime)) {
        //     $newStatus .= ' | early out';
        // }

        // // 🕒 Short working hours (15 min grace)
        // if ($workedMinutes < $totalShiftMinutes - 15) {
        //     $newStatus .= ' | short hour';
        // }

        // $attendance->status = trim($newStatus, ' |');
        // $attendance->save();

        // return redirect()->back()->with([
        //     'message' => 'Checked out successfully',
        //     'status' => 'success'
        // ]);

        $checkOutTime = now();
        $attendance->check_out = $checkOutTime;

            // calculate working hours
        $shift = $user->relation->shiftTiming;
        $startTime = Carbon::parse($shift->start_time);
        $endTime = Carbon::parse($shift->end_time);
        $checkIn = Carbon::parse($attendance->check_in);
        $checkOut = Carbon::parse($checkOutTime);

        $totalShiftMinutes = $startTime->diffInMinutes($endTime);
        $workedMinutes = $checkIn->diffInMinutes($checkOut);

        $newStatus = $attendance->status;

        // Check for early checkout
        if ($checkOut->lt($endTime)) {
            $newStatus .= ' | early out';
        }

        // Check for short working hours
        if ($workedMinutes < $totalShiftMinutes) { // allow 15 min grace
            $newStatus .= ' | short hour';
        }

        $attendance->status = $newStatus;
        $attendance->save();

        return redirect()->back()->with([
            'message' => 'Checked out successfully',
            'status' => 'success',
            // 'time' => $checkOutTime
        ]);
    }

    private function determineStatus($user, $checkInTime)
    {
        $graceTime   = optional(optional($user->relation)->shiftTiming)->grace_period ?? 0;
        $officeStart = optional(optional($user->relation)->shiftTiming)->start_time;

        if (!$officeStart) return 'on time';

        $lateThreshold = Carbon::parse($officeStart)->addMinutes($graceTime);
        $checkIn       = Carbon::parse($checkInTime);

        return $checkIn->gt($lateThreshold) ? 'late' : 'on time';
    }

    // Admin Methods
    public function adminReports(Request $request)
    {
        // $this->authorize('admin');

        $userId = $request->get('user_id');
        $date   = $request->get('date');
        $month  = $request->get('month');

        $query = Attendance::with('user');

        if ($date) {
            $query->whereDate('date', $date);
        } elseif ($month) {
            $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
            $endOfMonth   = Carbon::parse($month . '-01')->endOfMonth();
            $query->whereBetween('date', [$startOfMonth, $endOfMonth]);
        } else {
            // Default to today if no date or month filter
            $date = now()->toDateString();
            $query->whereDate('date', $date);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(50)->appends($request->except('page'));
        $users = User::where('user_role', 'user')->get();

        return view('modules.admin.attendance.admin-list', compact('attendances', 'users', 'date', 'month', 'userId'));
    }

    public function userMonthlyReport(Request $request)
    {
        $users           = User::where('user_role', 'user')->orderBy('full_name')->get();
        $selectedUser    = null;
        $report          = [];
        $summary         = null;
        $availableMonths = [];

        $selectedUserId = $request->get('user_id');
        $selectedYear   = $request->get('year', now()->year);
        $selectedMonth  = $request->get('month');
        $offDays        = ['Sunday', 'Saturday'];

        if ($selectedUserId) {
            $selectedUser = User::find($selectedUserId);

            $availableMonths = Attendance::where('user_id', $selectedUserId)
                ->whereYear('date', $selectedYear)
                ->selectRaw('MONTH(date) as month_num')
                ->groupBy('month_num')
                ->orderBy('month_num')
                ->get()
                ->map(fn($r) => [
                    'num'   => $r->month_num,
                    'label' => Carbon::createFromDate($selectedYear, $r->month_num, 1)->format('F Y'),
                    'value' => Carbon::createFromDate($selectedYear, $r->month_num, 1)->format('Y-m'),
                ]);

            if ($selectedMonth) {
                $startOfMonth      = Carbon::parse($selectedMonth . '-01')->startOfMonth();
                $endOfMonth        = Carbon::parse($selectedMonth . '-01')->endOfMonth();
                $attendanceRecords = Attendance::where('user_id', $selectedUserId)
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->get()
                    ->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d'));

                $shift      = optional(optional($selectedUser->relation)->shiftTiming);
                $shiftStart = $shift?->start_time ? Carbon::parse($shift->start_time) : null;
                $shiftEnd   = $shift?->end_time   ? Carbon::parse($shift->end_time)   : null;
                // Daily shift minutes (required per working day)
                $dailyShiftMins = ($shiftStart && $shiftEnd) ? $shiftStart->diffInMinutes($shiftEnd) : null;

                $totalWorkingDays = $presentDays = $absentDays = $lateDays = $totalShiftMins = $totalWorkedMins = $totalShortMins = 0;

                foreach (CarbonPeriod::create($startOfMonth, $endOfMonth) as $date) {
                    if ($date->gt(now())) break;
                    $dayName  = $date->format('l');
                    $dateKey  = $date->format('Y-m-d');
                    $isOffDay = in_array($dayName, $offDays);

                    if ($isOffDay) {
                        $report[] = ['date' => $date->format('D, d M Y'), 'day' => $dayName, 'status' => 'Off Day',
                            'check_in' => null, 'check_out' => null, 'worked_mins' => null, 'short_mins' => null, 'is_off' => true];
                        continue;
                    }

                    $totalWorkingDays++;

                    if (isset($attendanceRecords[$dateKey])) {
                        $rec        = $attendanceRecords[$dateKey];
                        $workedMins = null;
                        $shortMins  = null;

                        if ($rec->check_in && $rec->check_out) {
                            $workedMins = Carbon::parse($rec->check_in)->diffInMinutes(Carbon::parse($rec->check_out));
                            if ($dailyShiftMins !== null) {
                                $shortMins       = max(0, $dailyShiftMins - $workedMins);
                                $totalShiftMins  += $dailyShiftMins;
                                $totalWorkedMins += $workedMins;
                                $totalShortMins  += $shortMins;
                            }
                        } elseif ($dailyShiftMins !== null) {
                            // checked in but no checkout — or absent with check-in: count full day as short
                        }

                        $presentDays++;
                        if (str_contains($rec->status ?? '', 'late')) $lateDays++;

                        $report[] = [
                            'date'            => $date->format('D, d M Y'),
                            'day'             => $dayName,
                            'status'          => $rec->status,
                            'check_in'        => $rec->check_in  ? Carbon::parse($rec->check_in)->format('h:i A')  : null,
                            'check_out'       => $rec->check_out ? Carbon::parse($rec->check_out)->format('h:i A') : null,
                            'daily_shift_mins'=> $dailyShiftMins,
                            'worked_mins'     => $workedMins,
                            'short_mins'      => $shortMins,
                            'is_off'          => false,
                        ];
                    } else {
                        $absentDays++;
                        $report[] = ['date' => $date->format('D, d M Y'), 'day' => $dayName, 'status' => 'Absent',
                            'check_in' => null, 'check_out' => null, 'worked_mins' => null, 'short_mins' => null, 'is_off' => false];
                    }
                }

                $summary = [
                    'total_working_days'  => $totalWorkingDays,
                    'present_days'        => $presentDays,
                    'absent_days'         => $absentDays,
                    'late_days'           => $lateDays,
                    'daily_shift_mins'    => $dailyShiftMins,    // per-day required (for display)
                    'total_shift_mins'    => $totalShiftMins,    // total required minutes
                    'total_worked_mins'   => $totalWorkedMins,   // total worked minutes
                    'total_short_mins'    => $totalShortMins,    // total short minutes
                ];
            }
        }

        return view('modules.admin.attendance.user-monthly-report', compact(
            'users', 'selectedUser', 'selectedUserId', 'selectedYear',
            'selectedMonth', 'availableMonths', 'report', 'summary'
        ));
    }

    public function allUsersMonthlyReport(Request $request)
    {
        // $this->authorize('admin');

        $selectedMonth = $request->get('month', now()->format('Y-m'));
        $selectedYear  = $request->get('year', now()->year);
        $startOfMonth  = Carbon::parse($selectedMonth . '-01')->startOfMonth();
        $endOfMonth    = Carbon::parse($selectedMonth . '-01')->endOfMonth();

        $offDays = ['Sunday', 'Saturday'];
        $period  = CarbonPeriod::create($startOfMonth, min($endOfMonth, now()));
        $totalWorkingDays = 0;
        foreach ($period as $d) {
            if (!in_array($d->format('l'), $offDays)) $totalWorkingDays++;
        }

        $users = User::where('user_role', 'user')
            ->orderBy('full_name')
            ->with(['attendances' => fn($q) => $q->whereBetween('date', [$startOfMonth, $endOfMonth]),
                    'relation.shiftTiming'])
            ->get();

        $usersReport = $users->map(function ($user) use ($totalWorkingDays, $offDays, $startOfMonth, $endOfMonth) {
            $attendances = $user->attendances->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d'));

            $presentDays = $absentDays = $lateDays = $totalWorkedMins = $totalShiftMins = 0;

            $shift      = optional(optional($user->relation)->shiftTiming);
            $shiftStart = $shift?->start_time ? Carbon::parse($shift->start_time) : null;
            $shiftEnd   = $shift?->end_time   ? Carbon::parse($shift->end_time)   : null;
            $shiftMins  = ($shiftStart && $shiftEnd) ? $shiftStart->diffInMinutes($shiftEnd) : null;

            foreach (CarbonPeriod::create($startOfMonth, min($endOfMonth, now())) as $date) {
                if (in_array($date->format('l'), $offDays)) continue;
                $dateKey = $date->format('Y-m-d');

                if (isset($attendances[$dateKey])) {
                    $rec = $attendances[$dateKey];
                    $presentDays++;
                    if (str_contains($rec->status ?? '', 'late')) $lateDays++;
                    if ($rec->check_in && $rec->check_out && $shiftMins) {
                        $totalWorkedMins += Carbon::parse($rec->check_in)->diffInMinutes(Carbon::parse($rec->check_out));
                        $totalShiftMins  += $shiftMins;
                    }
                } else {
                    $absentDays++;
                }
            }

            return [
                'id'                 => $user->id,
                'name'               => $user->full_name,
                'email'              => $user->email,
                'department'         => optional(optional($user->relation)->department)->name ?? '-',
                'total_working_days' => $totalWorkingDays,
                'present_days'       => $presentDays,
                'absent_days'        => $absentDays,
                'late_days'          => $lateDays,
                'total_shift_hours'  => $totalShiftMins  > 0 ? round($totalShiftMins  / 60, 2) : null,
                'total_worked_hours' => $totalWorkedMins > 0 ? round($totalWorkedMins / 60, 2) : null,
                'attendance_pct'     => $totalWorkingDays > 0 ? round(($presentDays / $totalWorkingDays) * 100) : 0,
            ];
        });

        $years = range(now()->year, max(now()->year - 3, 2023));

        return view('modules.admin.attendance.all-users-report', compact(
            'usersReport', 'selectedYear', 'selectedMonth', 'totalWorkingDays', 'years'
        ));
    }

    public function editAttendance(Request $request, $id)
    {
        $this->authorize('admin');

        $attendance = Attendance::findOrFail($id);

        $request->validate([
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,half_day'
        ]);

        $attendance->update([
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'status' => $request->status
        ]);

        return response()->json(['message' => 'Attendance updated successfully']);
    }
}
