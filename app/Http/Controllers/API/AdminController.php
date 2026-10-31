<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OffDay;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The administrator half of the mobile app: today's turnout, who is missing,
 * leave approvals and per-employee reports.
 */
class AdminController extends Controller
{
    public function __construct(private AttendanceService $attendance)
    {
    }

    /**
     * Everything the admin home screen shows, in one call.
     */
    public function dashboard(Request $request)
    {
        $today = now()->toDateString();

        $records = Attendance::with('user:id,full_name,employee_code')
            ->whereDate('date', $today)
            ->get();

        $onLeave = LeaveRequest::approved()
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->with('user:id,full_name')
            ->get();

        $holiday = OffDay::whereDate('date', $today)->first();

        $employees = User::where('user_role', 'user')->where('is_active', true)->count();
        $present   = $records->whereNotNull('check_in')->count();

        $checkedIn  = $records->whereNotNull('check_in')->pluck('user_id')->all();
        $onLeaveIds = $onLeave->pluck('user_id')->all();

        $missing = User::where('user_role', 'user')
            ->where('is_active', true)
            ->whereNotIn('id', array_merge($checkedIn, $onLeaveIds))
            ->with('relation.department')
            ->get()
            ->reject(fn (User $u) => $holiday || in_array(now()->format('l'), $u->weeklyOffDays()))
            ->values();

        return response()->json([
            'status' => true,
            'data'   => [
                'date'    => $today,
                'label'   => now()->format('l, j F Y'),
                'holiday' => $holiday?->title,
                'stats'   => [
                    'employees'   => $employees,
                    'present'     => $present,
                    'late'        => $records->where('is_late', true)->count(),
                    'checked_out' => $records->whereNotNull('check_out')->count(),
                    'on_leave'    => $onLeave->count(),
                    'missing'     => $missing->count(),
                    'rate'        => $employees > 0 ? (int) round(($present / $employees) * 100) : 0,
                ],
                'pending_leaves' => LeaveRequest::pending()->count(),
                'recent_punches' => $records->whereNotNull('check_in')
                    ->sortByDesc('check_in')
                    ->take(10)
                    ->values()
                    ->map(fn (Attendance $a) => [
                        'id'        => $a->id,
                        'name'      => $a->user?->full_name ?? '—',
                        'check_in'  => Carbon::parse($a->check_in)->format('h:i A'),
                        'check_out' => $a->check_out ? Carbon::parse($a->check_out)->format('h:i A') : null,
                        'is_late'   => (bool) $a->is_late,
                        'status'    => $a->status_label,
                    ]),
                'missing' => $missing->take(10)->map(fn (User $u) => [
                    'id'         => $u->id,
                    'name'       => $u->full_name,
                    'department' => optional(optional($u->relation)->department)->name,
                ]),
                'on_leave' => $onLeave->take(10)->map(fn (LeaveRequest $l) => [
                    'id'    => $l->id,
                    'name'  => $l->user?->full_name ?? '—',
                    'type'  => ucfirst($l->type),
                    'until' => $l->end_date->format('d M'),
                ]),
                'trend' => $this->trend(),
            ],
        ]);
    }

    public function employees(Request $request)
    {
        $search = $request->get('q');
        $today  = now()->toDateString();

        $users = User::where('user_role', 'user')
            ->when($search, fn ($q) => $q->where(fn ($w) => $w
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('employee_code', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->with('relation.department', 'relation.designation')
            ->orderBy('full_name')
            ->get();

        $records = Attendance::whereDate('date', $today)
            ->whereIn('user_id', $users->pluck('id'))
            ->get()
            ->keyBy('user_id');

        return response()->json([
            'status' => true,
            'data'   => $users->map(function (User $user) use ($records) {
                $record = $records[$user->id] ?? null;

                return [
                    'id'          => $user->id,
                    'name'        => $user->full_name,
                    'code'        => $user->employee_code,
                    'email'       => $user->email,
                    'department'  => optional(optional($user->relation)->department)->name,
                    'designation' => optional(optional($user->relation)->designation)->name,
                    'is_active'   => (bool) $user->is_active,
                    'today'       => $record ? [
                        'check_in'  => $record->check_in ? Carbon::parse($record->check_in)->format('h:i A') : null,
                        'check_out' => $record->check_out ? Carbon::parse($record->check_out)->format('h:i A') : null,
                        'is_late'   => (bool) $record->is_late,
                        'status'    => $record->status_label,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * One employee's month, using the same report the portal renders.
     */
    public function employeeReport(Request $request, $id)
    {
        $user = User::with('relation.department', 'relation.designation', 'relation.shiftTiming')
            ->findOrFail($id);

        $month = $request->get('month');

        if (!$month || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $report = $this->attendance->monthlyReport($user, $month);

        return response()->json([
            'status' => true,
            'data'   => [
                'employee' => [
                    'id'          => $user->id,
                    'name'        => $user->full_name,
                    'code'        => $user->employee_code,
                    'email'       => $user->email,
                    'phone'       => $user->phone,
                    'department'  => optional(optional($user->relation)->department)->name,
                    'designation' => optional(optional($user->relation)->designation)->name,
                    'is_active'   => (bool) $user->is_active,
                ],
                'month'   => $month,
                'label'   => Carbon::parse($month . '-01')->format('F Y'),
                'summary' => $report['summary'],
                'days'    => array_reverse($report['days']),
            ],
        ]);
    }

    public function leaves(Request $request)
    {
        $status = $request->get('status', 'pending');

        $query = LeaveRequest::with('user:id,full_name,employee_code', 'approver:id,full_name')
            ->latest('created_at');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'counts' => [
                    'pending'  => LeaveRequest::where('status', 'pending')->count(),
                    'approved' => LeaveRequest::where('status', 'approved')->count(),
                    'rejected' => LeaveRequest::where('status', 'rejected')->count(),
                ],
                'leaves' => $query->limit(100)->get()->map(fn (LeaveRequest $l) => [
                    'id'          => $l->id,
                    'employee'    => $l->user?->full_name ?? '—',
                    'code'        => $l->user?->employee_code,
                    'type'        => $l->type,
                    'type_label'  => ucfirst($l->type),
                    'range_label' => $l->start_date->format('d M') . ' – ' . $l->end_date->format('d M Y'),
                    'days'        => (int) $l->days,
                    'reason'      => $l->reason,
                    'status'      => $l->status,
                    'admin_notes' => $l->admin_notes,
                    'approver'    => $l->approver?->full_name,
                    'applied_on'  => $l->created_at->format('d M Y'),
                ]),
            ],
        ]);
    }

    public function decideLeave(Request $request, $id)
    {
        $validated = $request->validate([
            'status'      => ['required', Rule::in(['approved', 'rejected'])],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $leave = LeaveRequest::findOrFail($id);

        if ($leave->status !== 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'That request has already been decided.',
            ], 422);
        }

        $leave->update([
            'status'      => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
            'approved_by' => $request->user()->id,
            'acted_at'    => now(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Leave request ' . $validated['status'] . '.',
        ]);
    }

    /**
     * Attendance records for one day, across everyone.
     */
    public function attendance(Request $request)
    {
        $date = $request->get('date');

        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = now()->toDateString();
        }

        $records = Attendance::with('user:id,full_name,employee_code')
            ->whereDate('date', $date)
            ->get()
            ->sortBy(fn (Attendance $a) => $a->user?->full_name)
            ->values();

        return response()->json([
            'status' => true,
            'data'   => [
                'date'    => $date,
                'label'   => Carbon::parse($date)->format('l, j F Y'),
                'records' => $records->map(fn (Attendance $a) => [
                    'id'        => $a->id,
                    'name'      => $a->user?->full_name ?? '—',
                    'code'      => $a->user?->employee_code,
                    'check_in'  => $a->check_in ? Carbon::parse($a->check_in)->format('h:i A') : null,
                    'check_out' => $a->check_out ? Carbon::parse($a->check_out)->format('h:i A') : null,
                    'worked'    => $a->worked_hours,
                    'status'    => $a->status_label,
                    'is_late'   => (bool) $a->is_late,
                    'source'    => $a->source,
                    // Where the punch was made, when the employee allowed it.
                    'check_in_location'  => $this->coords($a->check_in_lat, $a->check_in_lng),
                    'check_out_location' => $this->coords($a->check_out_lat, $a->check_out_lng),
                ]),
            ],
        ]);
    }

    /**
     * A punch's coordinates, or null when the employee did not share them.
     *
     * @return array{lat: float, lng: float}|null
     */
    private function coords($lat, $lng): ?array
    {
        if ($lat === null || $lng === null) {
            return null;
        }

        return ['lat' => (float) $lat, 'lng' => (float) $lng];
    }

    /**
     * Present/late counts for the last 14 days, for the dashboard chart.
     */
    private function trend(): array
    {
        $start = now()->copy()->subDays(13)->startOfDay();

        $rows = Attendance::whereBetween('date', [$start, now()])
            ->selectRaw('date, COUNT(*) as total, SUM(is_late) as late')
            ->groupBy('date')
            ->get()
            ->keyBy(fn ($r) => Carbon::parse($r->date)->format('Y-m-d'));

        $trend = [];

        foreach (CarbonPeriod::create($start, now()) as $date) {
            $key = $date->format('Y-m-d');

            $trend[] = [
                'label'   => $date->format('d M'),
                'present' => (int) ($rows[$key]->total ?? 0),
                'late'    => (int) ($rows[$key]->late ?? 0),
            ];
        }

        return $trend;
    }
}
