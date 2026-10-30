<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\OffDay;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $leaves = $user->leaveRequests()
            ->with('approver:id,full_name')
            ->latest('start_date')
            ->get()
            ->map(fn (LeaveRequest $l) => $this->present($l));

        return response()->json([
            'status' => true,
            'data'   => [
                'leaves'  => $leaves,
                'types'   => LeaveRequest::TYPES,
                'balance' => $this->balance($request),
            ],
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

        $user  = $request->user();
        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);

        if ((int) $start->diffInDays($end) > 90) {
            return response()->json([
                'status'  => false,
                'message' => 'A single request cannot span more than 90 days.',
            ], 422);
        }

        $clash = $user->leaveRequests()
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->exists();

        if ($clash) {
            return response()->json([
                'status'  => false,
                'message' => 'You already have a leave request covering those dates.',
            ], 422);
        }

        $days = $this->workingDays($request, $start, $end);

        if ($days === 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Those dates are already non-working days.',
            ], 422);
        }

        $leave = LeaveRequest::create([
            'user_id'    => $user->id,
            'type'       => $validated['type'],
            'start_date' => $start,
            'end_date'   => $end,
            'days'       => $days,
            'reason'     => $validated['reason'],
            'status'     => 'pending',
        ]);

        return response()->json([
            'status'  => true,
            'message' => "Leave request submitted for {$days} working day(s).",
            'data'    => $this->present($leave),
        ], 201);
    }

    public function cancel(Request $request, $id)
    {
        $leave = LeaveRequest::where('user_id', $request->user()->id)->findOrFail($id);

        if ($leave->status !== 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'Only pending requests can be cancelled.',
            ], 422);
        }

        $leave->update(['status' => 'cancelled', 'acted_at' => now()]);

        return response()->json([
            'status'  => true,
            'message' => 'Leave request cancelled.',
            'data'    => $this->present($leave->fresh()),
        ]);
    }

    private function present(LeaveRequest $leave): array
    {
        return [
            'id'          => $leave->id,
            'type'        => $leave->type,
            'type_label'  => ucfirst($leave->type),
            'start_date'  => $leave->start_date->toDateString(),
            'end_date'    => $leave->end_date->toDateString(),
            'range_label' => $leave->start_date->format('d M') . ' – ' . $leave->end_date->format('d M Y'),
            'days'        => (int) $leave->days,
            'reason'      => $leave->reason,
            'status'      => $leave->status,
            'admin_notes' => $leave->admin_notes,
            'approver'    => $leave->approver?->full_name,
            'acted_at'    => optional($leave->acted_at)->toIso8601String(),
            'created_at'  => $leave->created_at->toIso8601String(),
        ];
    }

    private function balance(Request $request): array
    {
        $user  = $request->user();
        $quota = (int) ($user->annual_leave_quota ?? 20);

        $used = (int) $user->leaveRequests()
            ->approved()
            ->whereBetween('start_date', [now()->startOfYear(), now()->endOfYear()])
            ->sum('days');

        return [
            'quota'     => $quota,
            'used'      => $used,
            'remaining' => max(0, $quota - $used),
            'pending'   => (int) $user->leaveRequests()->pending()->sum('days'),
        ];
    }

    private function workingDays(Request $request, Carbon $start, Carbon $end): int
    {
        $weeklyOff = $request->user()->weeklyOffDays();

        $holidays = OffDay::whereBetween('date', [$start, $end])
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->all();

        $days = 0;

        foreach (CarbonPeriod::create($start, $end) as $date) {
            if (in_array($date->format('l'), $weeklyOff) || in_array($date->format('Y-m-d'), $holidays)) {
                continue;
            }

            $days++;
        }

        return $days;
    }
}
