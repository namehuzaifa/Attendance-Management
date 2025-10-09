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
        $offDays = ['Sunday'];

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
        $graceTime =  $user->relation->shiftTiming->grace_period ?? 0; // 30 minutes
        $officeStart = $user->relation->shiftTiming->start_time;

        $lateThreshold = Carbon::parse($officeStart)->addMinutes($graceTime);
        $checkIn = Carbon::parse($checkInTime);

        return $checkIn->gt($lateThreshold) ? 'late' : 'on time';
    }

    // Admin Methods
    public function adminReports(Request $request)
    {
        // $this->authorize('admin');

        $month = $request->get('month', now()->format('Y-m'));
        $userId = $request->get('user_id');

        $query = Attendance::with('user')
            ->whereYear('date', substr($month, 0, 4))
            ->whereMonth('date', substr($month, 5, 2));

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(50);
        $users = User::where('user_role', 'user')->get();

        return view('modules.admin.attendance.admin-list', compact('attendances', 'users', 'month', 'userId'));
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
