<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OffDay;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendance)
    {
    }

    /**
     * Everything the app's home screen needs in one round trip.
     */
    public function home(Request $request)
    {
        $user   = $request->user();
        $date   = $this->attendance->businessDate($user);
        $record = Attendance::where('user_id', $user->id)->whereDate('date', $date)->first();
        $window = $this->attendance->shiftWindow($user, $date);
        $report = $this->attendance->monthlyReport($user, now()->format('Y-m'));

        $holiday   = OffDay::whereDate('date', $date)->first();
        $isOffDay  = in_array($date->format('l'), $user->weeklyOffDays());

        return response()->json([
            'status' => true,
            'data'   => [
                'server_time'   => now()->toIso8601String(),
                'business_date' => $date->toDateString(),
                'today'         => $this->presentRecord($record),
                'shift'         => $window ? [
                    'start'           => $window['start']->toIso8601String(),
                    'end'             => $window['end']->toIso8601String(),
                    'start_label'     => $window['start']->format('h:i A'),
                    'end_label'       => $window['end']->format('h:i A'),
                    'grace_period'    => $window['grace'],
                    'total_minutes'   => $window['minutes'],
                ] : null,
                'can_check_in'  => !$record || !$record->check_in,
                'can_check_out' => (bool) ($record && $record->check_in && !$record->check_out),
                'is_off_day'    => $isOffDay,
                'holiday'       => $holiday?->title,
                'ip_restricted' => $this->attendance->ipRestrictionEnabled(),
                'summary'       => $report['summary'],
                'recent'        => array_slice(array_reverse($report['days']), 0, 7),
                'leave_balance' => $this->leaveBalance($request),
                'pending_leaves' => $user->leaveRequests()->pending()->count(),
            ],
        ]);
    }

    public function today(Request $request)
    {
        $user   = $request->user();
        $date   = $this->attendance->businessDate($user);
        $record = Attendance::where('user_id', $user->id)->whereDate('date', $date)->first();

        return response()->json([
            'status' => true,
            'data'   => [
                'business_date' => $date->toDateString(),
                'today'         => $this->presentRecord($record),
                'can_check_in'  => !$record || !$record->check_in,
                'can_check_out' => (bool) ($record && $record->check_in && !$record->check_out),
            ],
        ]);
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $result = $this->attendance->checkIn($request->user(), [
            'ip'     => $request->ip(),
            'lat'    => $request->input('latitude'),
            'lng'    => $request->input('longitude'),
            'source' => 'mobile',
        ]);

        return response()->json([
            'status'  => $result['ok'],
            'message' => $result['message'],
            'data'    => isset($result['attendance']) ? $this->presentRecord($result['attendance']) : null,
        ], $result['ok'] ? 200 : 422);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $result = $this->attendance->checkOut($request->user(), [
            'ip'     => $request->ip(),
            'lat'    => $request->input('latitude'),
            'lng'    => $request->input('longitude'),
            'source' => 'mobile',
        ]);

        return response()->json([
            'status'  => $result['ok'],
            'message' => $result['message'],
            'data'    => isset($result['attendance']) ? $this->presentRecord($result['attendance']) : null,
        ], $result['ok'] ? 200 : 422);
    }

    public function month(Request $request)
    {
        $month = $request->get('month');

        if (!$month || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $report = $this->attendance->monthlyReport($request->user(), $month);

        return response()->json([
            'status' => true,
            'data'   => [
                'month'   => $month,
                'label'   => Carbon::parse($month . '-01')->format('F Y'),
                'days'    => array_reverse($report['days']),
                'summary' => $report['summary'],
            ],
        ]);
    }

    public function holidays(Request $request)
    {
        $year = (int) $request->get('year', now()->year);

        $holidays = OffDay::whereYear('date', $year)
            ->orderBy('date')
            ->get()
            ->map(fn (OffDay $h) => [
                'id'          => $h->id,
                'title'       => $h->title,
                'date'        => $h->date->toDateString(),
                'label'       => $h->date->format('D, d M Y'),
                'description' => $h->description,
                'is_past'     => $h->date->isPast(),
            ]);

        return response()->json(['status' => true, 'data' => $holidays]);
    }

    private function presentRecord(?Attendance $record): ?array
    {
        if (!$record) {
            return null;
        }

        return [
            'id'              => $record->id,
            'date'            => Carbon::parse($record->date)->toDateString(),
            'check_in'        => $record->check_in ? Carbon::parse($record->check_in)->toIso8601String() : null,
            'check_out'       => $record->check_out ? Carbon::parse($record->check_out)->toIso8601String() : null,
            'check_in_label'  => $record->check_in ? Carbon::parse($record->check_in)->format('h:i A') : null,
            'check_out_label' => $record->check_out ? Carbon::parse($record->check_out)->format('h:i A') : null,
            'status'          => $record->status_label,
            'is_late'         => (bool) $record->is_late,
            'is_early_out'    => (bool) $record->is_early_out,
            'is_short_hour'   => (bool) $record->is_short_hour,
            'late_minutes'    => $record->late_minutes,
            'worked_minutes'  => $record->worked_minutes,
            'worked_label'    => $record->worked_hours,
            'source'          => $record->source,
        ];
    }

    private function leaveBalance(Request $request): array
    {
        $user  = $request->user();
        $quota = (int) ($user->annual_leave_quota ?? 20);

        $used = (int) LeaveRequest::where('user_id', $user->id)
            ->approved()
            ->whereBetween('start_date', [now()->startOfYear(), now()->endOfYear()])
            ->sum('days');

        return ['quota' => $quota, 'used' => $used, 'remaining' => max(0, $quota - $used)];
    }
}
