<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
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
            redirect()->back()->with([
                'message' => 'Already checked in today',
                'status' => 'failed'
            ]);
        }

        $checkInTime = now()->format('H:i:s');

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

        if (!$attendance || !$attendance->check_in) {
            // return response()->json(['error' => 'Must check in first'], 400);
            redirect()->back()->with([
                'message' => 'Must check in first',
                'status' => 'failed'
            ]);
        }

        if ($attendance->check_out) {
            // return response()->json(['error' => 'Already checked out'], 400);
            redirect()->back()->with([
                'message' => 'Already checked out',
                'status' => 'failed'
            ]);
        }

        $checkOutTime = now()->format('H:i:s');
        $attendance->check_out = $checkOutTime;
        // $attendance->update(['check_out' => $checkOutTime]);

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
        if ($workedMinutes < $totalShiftMinutes - 15) { // allow 15 min grace
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
