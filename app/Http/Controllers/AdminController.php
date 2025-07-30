<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OffDay;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $this->authorize('admin');

        $stats = [
            'total_employees' => User::where('role', 'employee')->count(),
            'present_today' => Attendance::whereDate('date', today())
                ->where('status', '!=', 'absent')->count(),
            'pending_leaves' => LeaveRequest::where('status', 'pending')->count(),
            'late_today' => Attendance::whereDate('date', today())
                ->where('status', 'late')->count()
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function users()
    {
        $this->authorize('admin');

        $users = User::where('role', 'employee')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function createUser(Request $request)
    {
        $this->authorize('admin');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'office_start_time' => 'required|date_format:H:i',
            'office_end_time' => 'required|date_format:H:i'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'office_start_time' => $request->office_start_time,
            'office_end_time' => $request->office_end_time,
            'role' => 'employee'
        ]);

        return redirect()->back()->with('success', 'User created successfully');
    }

    public function updateUserTiming(Request $request, $id)
    {
        $this->authorize('admin');

        $request->validate([
            'office_start_time' => 'required|date_format:H:i',
            'office_end_time' => 'required|date_format:H:i'
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'office_start_time' => $request->office_start_time,
            'office_end_time' => $request->office_end_time
        ]);

        return response()->json(['message' => 'User timing updated']);
    }

    public function reports(Request $request)
    {
        $this->authorize('admin');

        $month = $request->get('month', now()->format('Y-m'));
        $userId = $request->get('user_id');

        $users = User::where('role', 'employee')->get();

        if ($userId) {
            $user = User::findOrFail($userId);
            $attendances = $user->attendances()
                ->whereYear('date', substr($month, 0, 4))
                ->whereMonth('date', substr($month, 5, 2))
                ->orderBy('date')
                ->get();

            $leaves = $user->leaveRequests()
                ->where(function($query) use ($month) {
                    $query->whereYear('start_date', substr($month, 0, 4))
                          ->whereMonth('start_date', substr($month, 5, 2));
                })
                ->get();
        } else {
            $attendances = Attendance::with('user')
                ->whereYear('date', substr($month, 0, 4))
                ->whereMonth('date', substr($month, 5, 2))
                ->get()
                ->groupBy('user_id');

            $leaves = LeaveRequest::with('user')
                ->where(function($query) use ($month) {
                    $query->whereYear('start_date', substr($month, 0, 4))
                          ->whereMonth('start_date', substr($month, 5, 2));
                })
                ->get()
                ->groupBy('user_id');
        }

        return view('admin.reports', compact('users', 'attendances', 'leaves', 'month', 'userId'));
    }

    public function offDays()
    {
        $this->authorize('admin');

        $offDays = OffDay::orderBy('date', 'desc')->paginate(20);

        return view('admin.off-days.index', compact('offDays'));
    }

    public function createOffDay(Request $request)
    {
        $this->authorize('admin');

        $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:holiday,weekend,special'
        ]);

        OffDay::create($request->all());

        return redirect()->back()->with('success', 'Off day created successfully');
    }
}
