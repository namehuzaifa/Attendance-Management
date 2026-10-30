<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\OffDay;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private AttendanceService $attendance)
    {
    }

    public function index()
    {
        return Auth::user()->isAdmin()
            ? $this->adminDashboard()
            : $this->employeeDashboard();
    }

    private function employeeDashboard()
    {
        $user  = Auth::user();
        $today = $this->attendance->businessDate($user);

        $todayRecord = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $window = $this->attendance->shiftWindow($user, $today);
        $report = $this->attendance->monthlyReport($user, now()->format('Y-m'));

        // Last 7 entries, newest first, for the timeline card.
        $recent = array_slice(array_reverse($report['days']), 0, 7);

        $upcomingHolidays = OffDay::whereDate('date', '>=', now())
            ->orderBy('date')
            ->limit(4)
            ->get();

        $leaveBalance = $this->leaveBalance($user);

        $weeklyOff = $user->weeklyOffDays();
        $schedule  = [];

        foreach (CarbonPeriod::create(now(), now()->copy()->addDays(6)) as $date) {
            $isOff = in_array($date->format('l'), $weeklyOff);
            $holiday = OffDay::whereDate('date', $date)->first();

            $schedule[] = [
                'date'    => $date->format('D, d M'),
                'day'     => $date->format('l'),
                'is_off'  => $isOff || $holiday !== null,
                'label'   => $holiday->title ?? ($isOff ? 'Weekly Off' : null),
                'start'   => (!$isOff && !$holiday && $window) ? $window['start']->format('h:i A') : null,
                'end'     => (!$isOff && !$holiday && $window) ? $window['end']->format('h:i A') : null,
                'is_today' => $date->isToday(),
            ];
        }

        return view('modules.admin.dashboard.index', [
            'todayRecord'      => $todayRecord,
            'window'           => $window,
            'canCheckIn'       => !$todayRecord || !$todayRecord->check_in,
            'canCheckOut'      => $todayRecord && $todayRecord->check_in && !$todayRecord->check_out,
            'summary'          => $report['summary'],
            'recent'           => $recent,
            'schedule'         => $schedule,
            'upcomingHolidays' => $upcomingHolidays,
            'leaveBalance'     => $leaveBalance,
            'pendingLeaves'    => $user->leaveRequests()->pending()->count(),
            'service'          => $this->attendance,
        ]);
    }

    private function adminDashboard()
    {
        $today = now()->toDateString();

        $employees = User::where('user_role', 'user')->where('is_active', true)->count();

        $todayRecords = Attendance::with('user')
            ->whereDate('date', $today)
            ->get();

        $present = $todayRecords->whereNotNull('check_in')->count();
        $late    = $todayRecords->where('is_late', true)->count();
        $out     = $todayRecords->whereNotNull('check_out')->count();

        $onLeaveToday = LeaveRequest::approved()
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->with('user')
            ->get();

        $isHoliday = OffDay::whereDate('date', $today)->first();

        // Everyone who is expected in today but has not punched yet.
        $checkedInIds = $todayRecords->whereNotNull('check_in')->pluck('user_id')->all();
        $onLeaveIds   = $onLeaveToday->pluck('user_id')->all();

        $missing = User::where('user_role', 'user')
            ->where('is_active', true)
            ->whereNotIn('id', array_merge($checkedInIds, $onLeaveIds))
            ->with('relation.department')
            ->get()
            ->reject(fn (User $u) => in_array(now()->format('l'), $u->weeklyOffDays()) || $isHoliday)
            ->values();

        // 14-day trend for the chart.
        $trendStart = now()->copy()->subDays(13)->startOfDay();

        $trendRows = Attendance::whereBetween('date', [$trendStart, now()])
            ->selectRaw('date, COUNT(*) as total, SUM(is_late) as late')
            ->groupBy('date')
            ->get()
            ->keyBy(fn ($r) => Carbon::parse($r->date)->format('Y-m-d'));

        $trend = [];
        foreach (CarbonPeriod::create($trendStart, now()) as $date) {
            $key = $date->format('Y-m-d');
            $trend[] = [
                'label'   => $date->format('d M'),
                'present' => (int) ($trendRows[$key]->total ?? 0),
                'late'    => (int) ($trendRows[$key]->late ?? 0),
            ];
        }

        $departments = Department::with('userRelations:id,user_id,department_id')
            ->orderBy('name')
            ->get()
            ->map(function (Department $department) use ($todayRecords) {
                $ids = $department->userRelations->pluck('user_id');

                return [
                    'name'    => $department->name,
                    'staff'   => $ids->count(),
                    'present' => $todayRecords->whereIn('user_id', $ids)->whereNotNull('check_in')->count(),
                ];
            });

        return view('modules.admin.dashboard.index', [
            'stats' => [
                'employees'    => $employees,
                'present'      => $present,
                'late'         => $late,
                'checked_out'  => $out,
                'on_leave'     => $onLeaveToday->count(),
                'missing'      => $missing->count(),
                'rate'         => $employees > 0 ? (int) round(($present / $employees) * 100) : 0,
            ],
            'isHoliday'     => $isHoliday,
            'recentPunches' => $todayRecords->whereNotNull('check_in')->sortByDesc('check_in')->take(8)->values(),
            'missing'       => $missing->take(8),
            'onLeaveToday'  => $onLeaveToday->take(6),
            'pendingLeaves' => LeaveRequest::pending()->with('user')->latest()->take(6)->get(),
            'pendingCount'  => LeaveRequest::pending()->count(),
            'trend'         => $trend,
            'departments'   => $departments,
            'service'       => $this->attendance,
        ]);
    }

    /**
     * @return array{quota: int, used: int, remaining: int}
     */
    private function leaveBalance(User $user): array
    {
        $quota = (int) ($user->annual_leave_quota ?? 20);

        $used = (int) $user->leaveRequests()
            ->approved()
            ->whereBetween('start_date', [now()->startOfYear(), now()->endOfYear()])
            ->sum('days');

        return ['quota' => $quota, 'used' => $used, 'remaining' => max(0, $quota - $used)];
    }
}
