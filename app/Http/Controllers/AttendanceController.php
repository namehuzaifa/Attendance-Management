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

        return view('attendance.index', compact('attendances', 'month'));
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
            return response()->json(['error' => 'Already checked in today'], 400);
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

        return response()->json([
            'message' => 'Checked in successfully',
            'time' => $checkInTime,
            'status' => $attendance->status
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
            return response()->json(['error' => 'Must check in first'], 400);
        }

        if ($attendance->check_out) {
            return response()->json(['error' => 'Already checked out'], 400);
        }

        $checkOutTime = now()->format('H:i:s');
        $attendance->update(['check_out' => $checkOutTime]);

        return response()->json([
            'message' => 'Checked out successfully',
            'time' => $checkOutTime
        ]);
    }

    private function determineStatus($user, $checkInTime)
    {
        $graceTime = 30; // 30 minutes
        $officeStart = $user->office_start_time;

        $lateThreshold = Carbon::parse($officeStart)->addMinutes($graceTime);
        $checkIn = Carbon::parse($checkInTime);

        return $checkIn->gt($lateThreshold) ? 'late' : 'present';
    }

    // Admin Methods
    public function adminIndex(Request $request)
    {
        $this->authorize('admin');

        $month = $request->get('month', now()->format('Y-m'));
        $userId = $request->get('user_id');

        $query = Attendance::with('user')
            ->whereYear('date', substr($month, 0, 4))
            ->whereMonth('date', substr($month, 5, 2));

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(50);
        $users = User::where('role', 'employee')->get();

        return view('admin.attendance.index', compact('attendances', 'users', 'month', 'userId'));
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
