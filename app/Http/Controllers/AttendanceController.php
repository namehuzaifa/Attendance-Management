<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendance)
    {
    }

    /**
     * The signed-in employee's own month.
     */
    public function index(Request $request)
    {
        $user  = Auth::user();
        $month = $this->safeMonth($request->get('month'));

        $report = $this->attendance->monthlyReport($user, $month);

        return view('modules.admin.attendance.list', [
            'days'    => $report['days'],
            'summary' => $report['summary'],
            'month'   => $month,
            'service' => $this->attendance,
        ]);
    }

    public function checkIn(Request $request)
    {
        $result = $this->attendance->checkIn(Auth::user(), [
            'ip'     => $request->ip(),
            'lat'    => $request->input('latitude'),
            'lng'    => $request->input('longitude'),
            'source' => 'web',
        ]);

        return redirect()->back()->with([
            'message' => $result['message'],
            'status'  => $result['ok'] ? 'success' : 'failed',
        ]);
    }

    public function checkOut(Request $request)
    {
        $result = $this->attendance->checkOut(Auth::user(), [
            'ip'     => $request->ip(),
            'lat'    => $request->input('latitude'),
            'lng'    => $request->input('longitude'),
            'source' => 'web',
        ]);

        return redirect()->back()->with([
            'message' => $result['message'],
            'status'  => $result['ok'] ? 'success' : 'failed',
        ]);
    }

    /**
     * Admin correction of a punch. The route for this existed but pointed at
     * a method that was never written, so every edit returned a 500.
     */
    public function updateAttendance(Request $request, $id)
    {
        $validated = $request->validate([
            'check_in'  => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'notes'     => ['nullable', 'string', 'max:500'],
        ]);

        $record = Attendance::with('user')->findOrFail($id);
        $date   = Carbon::parse($record->date);

        $checkIn = !empty($validated['check_in'])
            ? $date->copy()->setTimeFromTimeString($validated['check_in'])
            : null;

        $checkOut = !empty($validated['check_out'])
            ? $date->copy()->setTimeFromTimeString($validated['check_out'])
            : null;

        // An overnight shift legitimately ends on the following calendar day.
        if ($checkIn && $checkOut && $checkOut->lessThanOrEqualTo($checkIn)) {
            $checkOut->addDay();
        }

        $window = $record->user ? $this->attendance->shiftWindow($record->user, $date) : null;

        $late = 0;
        if ($checkIn && $window) {
            $threshold = $window['start']->copy()->addMinutes($window['grace']);
            $late      = $checkIn->greaterThan($threshold) ? (int) $threshold->diffInMinutes($checkIn) : 0;
        }

        $worked = ($checkIn && $checkOut) ? (int) $checkIn->diffInMinutes($checkOut) : null;

        $record->update([
            'check_in'       => $checkIn,
            'check_out'      => $checkOut,
            'notes'          => $validated['notes'] ?? $record->notes,
            'status'         => $late > 0 ? 'late' : 'on time',
            'is_late'        => $late > 0,
            'late_minutes'   => $late > 0 ? $late : null,
            'worked_minutes' => $worked,
            'is_early_out'   => ($checkOut && $window) ? $checkOut->lessThan($window['end']) : false,
            'is_short_hour'  => ($worked !== null && $window) ? $worked < ($window['minutes'] - $window['grace']) : false,
            'edited_by'      => Auth::id(),
        ]);

        return redirect()->back()->with([
            'message' => 'Attendance updated successfully.',
            'status'  => 'success',
        ]);
    }

    // ---------------------------------------------------------------- admin

    public function adminReports(Request $request)
    {
        $userId       = $request->get('user_id');
        $departmentId = $request->get('department_id');
        $date         = $request->get('date');
        $month        = $request->get('month');

        $query = Attendance::with('user.relation.department');

        if ($date) {
            $query->whereDate('date', $date);
        } elseif ($month) {
            $month = $this->safeMonth($month);
            $query->whereBetween('date', [
                Carbon::parse($month . '-01')->startOfMonth(),
                Carbon::parse($month . '-01')->endOfMonth(),
            ]);
        } else {
            $date = now()->toDateString();
            $query->whereDate('date', $date);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($departmentId) {
            $query->whereHas('user.relation', fn ($q) => $q->where('department_id', $departmentId));
        }

        $attendances = $query->orderByDesc('date')->orderBy('user_id')
            ->paginate(50)
            ->appends($request->except('page'));

        return view('modules.admin.attendance.admin-list', [
            'attendances'  => $attendances,
            'users'        => User::where('user_role', 'user')->orderBy('full_name')->get(),
            'departments'  => Department::orderBy('name')->get(),
            'date'         => $date,
            'month'        => $month,
            'userId'       => $userId,
            'departmentId' => $departmentId,
            'service'      => $this->attendance,
        ]);
    }

    public function userMonthlyReport(Request $request)
    {
        $users          = User::where('user_role', 'user')->orderBy('full_name')->get();
        $selectedUserId = $request->get('user_id');
        $selectedMonth  = $this->safeMonth($request->get('month'));

        $selectedUser = $selectedUserId ? User::find($selectedUserId) : null;
        $report       = $selectedUser ? $this->attendance->monthlyReport($selectedUser, $selectedMonth) : null;

        return view('modules.admin.attendance.user-monthly-report', [
            'users'        => $users,
            'selectedUser' => $selectedUser,
            'month'        => $selectedMonth,
            'days'         => $report['days'] ?? [],
            'summary'      => $report['summary'] ?? null,
            'service'      => $this->attendance,
        ]);
    }

    public function allUsersMonthlyReport(Request $request)
    {
        $month = $this->safeMonth($request->get('month'));

        return view('modules.admin.attendance.all-users-report', [
            'usersReport' => $this->buildAllUsersReport($month),
            'month'       => $month,
            'service'     => $this->attendance,
        ]);
    }

    /**
     * CSV of the all-employees month — the portal had no way to get numbers
     * out for payroll, which meant retyping them by hand.
     */
    public function exportMonthly(Request $request): StreamedResponse
    {
        $month = $this->safeMonth($request->get('month'));
        $rows  = $this->buildAllUsersReport($month);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Employee', 'Code', 'Email', 'Department', 'Working Days', 'Present',
                'Absent', 'Leave', 'Late', 'Early Out', 'Short Hour',
                'Worked Hours', 'Required Hours', 'Attendance %',
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['name'], $r['code'], $r['email'], $r['department'],
                    $r['working_days'], $r['present'], $r['absent'], $r['leave'],
                    $r['late'], $r['early_out'], $r['short_hour'],
                    round($r['worked_minutes'] / 60, 2),
                    round($r['required_minutes'] / 60, 2),
                    $r['attendance_pct'],
                ]);
            }

            fclose($out);
        }, 'attendance-' . $month . '.csv', ['Content-Type' => 'text/csv']);
    }

    private function buildAllUsersReport(string $month): array
    {
        return User::where('user_role', 'user')
            ->with('relation.department', 'relation.shiftTiming')
            ->orderBy('full_name')
            ->get()
            ->map(function (User $user) use ($month) {
                $summary = $this->attendance->monthlyReport($user, $month)['summary'];

                return array_merge($summary, [
                    'id'         => $user->id,
                    'name'       => $user->full_name,
                    'code'       => $user->employee_code,
                    'email'      => $user->email,
                    'department' => optional(optional($user->relation)->department)->name ?? '—',
                ]);
            })
            ->all();
    }

    /**
     * Guards the month filter — a hand-edited query string used to throw an
     * unhandled parse exception straight at the user.
     */
    private function safeMonth(?string $month): string
    {
        if ($month && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            return $month;
        }

        return now()->format('Y-m');
    }
}
