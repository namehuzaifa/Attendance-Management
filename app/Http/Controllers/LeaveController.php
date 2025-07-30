<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $leaves = $user->leaveRequests()->latest()->paginate(10);

        return view('leave.index', compact('leaves'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:sick,casual,annual,emergency',
            'reason' => 'required|string|max:500'
        ]);

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $request->type,
            'reason' => $request->reason
        ]);

        return redirect()->back()->with('success', 'Leave request submitted successfully');
    }

    // Admin Methods
    public function adminIndex()
    {
        $this->authorize('admin');

        $leaves = LeaveRequest::with('user')->latest()->paginate(20);

        return view('admin.leave.index', compact('leaves'));
    }

    public function updateStatus(Request $request, $id)
    {
        $this->authorize('admin');

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string'
        ]);

        $leave = LeaveRequest::findOrFail($id);
        $leave->update([
            'status' => $request->status,
            'approved_by' => Auth::id(),
            'admin_notes' => $request->admin_notes
        ]);

        return response()->json(['message' => 'Leave request updated']);
    }
}
