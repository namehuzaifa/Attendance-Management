<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\OffDay;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LeaveController extends Controller
{
    /**
     * An employee's own requests, with the balance they have left.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $leaves = $user->leaveRequests()
            ->with('approver')
            ->latest('start_date')
            ->paginate(15);

        return view('modules.admin.leave.list', [
            'leaves'  => $leaves,
            'balance' => $this->balanceFor($user),
            'types'   => LeaveRequest::TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'       => ['required', Rule::in(LeaveRequest::TYPES)],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'reason'     => ['required', 'string', 'max:500'],
        ]);

        $user  = Auth::user();
        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);

        // Carbon 3 signs this difference, so measure forwards from the start.
        if ((int) $start->diffInDays($end) > 90) {
            return back()->withInput()->with([
                'status'  => 'failed',
                'message' => 'A single request cannot span more than 90 days.',
            ]);
        }

        // Stop two requests claiming the same day — the old module had no
        // overlap check at all, so a day could be booked repeatedly.
        $clash = $user->leaveRequests()
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->exists();

        if ($clash) {
            return back()->withInput()->with([
                'status'  => 'failed',
                'message' => 'You already have a leave request covering those dates.',
            ]);
        }

        $days = $this->workingDaysBetween($user, $start, $end);

        if ($days === 0) {
            return back()->withInput()->with([
                'status'  => 'failed',
                'message' => 'Those dates are already non-working days.',
            ]);
        }

        LeaveRequest::create([
            'user_id'    => $user->id,
            'type'       => $validated['type'],
            'start_date' => $start,
            'end_date'   => $end,
            'days'       => $days,
            'reason'     => $validated['reason'],
            'status'     => 'pending',
        ]);

        return back()->with([
            'status'  => 'success',
            'message' => "Leave request submitted for {$days} working day(s).",
        ]);
    }

    /**
     * Withdraw a request that has not been decided yet.
     */
    public function cancel($id)
    {
        $leave = LeaveRequest::where('user_id', Auth::id())->findOrFail($id);

        if ($leave->status !== 'pending') {
            return back()->with([
                'status'  => 'failed',
                'message' => 'Only pending requests can be cancelled.',
            ]);
        }

        $leave->update(['status' => 'cancelled', 'acted_at' => now()]);

        return back()->with(['status' => 'success', 'message' => 'Leave request cancelled.']);
    }

    // ---------------------------------------------------------------- admin

    public function adminIndex(Request $request)
    {
        $status = $request->get('status', 'pending');
        $userId = $request->get('user_id');

        $query = LeaveRequest::with('user', 'approver')->latest('created_at');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return view('modules.admin.leave.admin-list', [
            'leaves'  => $query->paginate(20)->appends($request->except('page')),
            'users'   => User::where('user_role', 'user')->orderBy('full_name')->get(),
            'status'  => $status,
            'userId'  => $userId,
            'counts'  => [
                'pending'  => LeaveRequest::where('status', 'pending')->count(),
                'approved' => LeaveRequest::where('status', 'approved')->count(),
                'rejected' => LeaveRequest::where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status'      => ['required', Rule::in(['approved', 'rejected'])],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $leave = LeaveRequest::findOrFail($id);

        if ($leave->status !== 'pending') {
            return back()->with([
                'status'  => 'failed',
                'message' => 'That request has already been decided.',
            ]);
        }

        $leave->update([
            'status'      => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
            'approved_by' => Auth::id(),
            'acted_at'    => now(),
        ]);

        return back()->with([
            'status'  => 'success',
            'message' => 'Leave request ' . $validated['status'] . '.',
        ]);
    }

    /**
     * Days actually deducted: weekly offs and public holidays inside the
     * range do not count against the employee.
     */
    private function workingDaysBetween(User $user, Carbon $start, Carbon $end): int
    {
        $weeklyOff = $user->weeklyOffDays();

        $holidays = OffDay::whereBetween('date', [$start, $end])
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->all();

        $days = 0;

        foreach (CarbonPeriod::create($start, $end) as $date) {
            if (in_array($date->format('l'), $weeklyOff)) {
                continue;
            }

            if (in_array($date->format('Y-m-d'), $holidays)) {
                continue;
            }

            $days++;
        }

        return $days;
    }

    /**
     * @return array{quota: int, used: int, remaining: int, by_type: array}
     */
    private function balanceFor(User $user): array
    {
        $yearStart = now()->startOfYear();
        $yearEnd   = now()->endOfYear();

        $approved = $user->leaveRequests()
            ->approved()
            ->whereBetween('start_date', [$yearStart, $yearEnd])
            ->get();

        $quota = (int) ($user->annual_leave_quota ?? 20);
        $used  = (int) $approved->sum('days');

        return [
            'quota'     => $quota,
            'used'      => $used,
            'remaining' => max(0, $quota - $used),
            'by_type'   => $approved->groupBy('type')->map->sum('days')->all(),
            'pending'   => (int) $user->leaveRequests()->pending()->sum('days'),
        ];
    }
}
