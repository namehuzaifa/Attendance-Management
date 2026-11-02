<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\LeaveRequest;
use App\Models\OffDay;
use App\Models\ShiftTiming;
use App\Models\User;
use App\Models\UserRelation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Walks every screen in the portal as both roles. Most of the bugs this
 * suite guards against were pages that returned a 500 the moment they were
 * opened, so a plain "does it render" assertion is the point.
 */
class PortalSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        $shift = ShiftTiming::create([
            'name'         => 'Day shift',
            'start_time'   => '09:00',
            'end_time'     => '17:00',
            'grace_period' => 15,
            'off_days'     => ['Saturday', 'Sunday'],
        ]);

        $this->employee = User::factory()->create();

        UserRelation::create([
            'user_id'         => $this->employee->id,
            'department_id'   => Department::create(['name' => 'Engineering'])->id,
            'designation_id'  => Designation::create(['name' => 'Developer'])->id,
            'shift_timing_id' => $shift->id,
            'manager_id'      => $this->admin->id,
        ]);
    }

    public function test_admin_screens_all_render(): void
    {
        $this->actingAs($this->admin);

        $routes = [
            '/dashboard',
            '/user-list',
            '/user-create',
            '/shift-timing-list',
            '/department-list',
            '/designation-list',
            '/ip-address-list',
            '/holiday-list',
            '/admin/leave',
            '/admin/attendance-reports',
            '/monthly/user-report',
            '/monthly/all-users-report',
        ];

        foreach ($routes as $route) {
            $this->get($route)->assertOk($route);
        }
    }

    public function test_employee_screens_all_render(): void
    {
        $this->actingAs($this->employee);

        foreach (['/dashboard', '/attendance', '/leave'] as $route) {
            $this->get($route)->assertOk($route);
        }

        $this->get('/user-edit/' . $this->employee->id)->assertOk();
    }

    public function test_employee_cannot_reach_admin_screens(): void
    {
        $this->actingAs($this->employee);

        $this->get('/user-list')->assertRedirect('login');
        $this->get('/admin/leave')->assertRedirect('login');
    }

    /**
     * The update route took any id and applied the posted role, so an
     * employee could promote themselves.
     */
    public function test_employee_cannot_edit_another_user_or_escalate_role(): void
    {
        $victim = User::factory()->create();

        $this->actingAs($this->employee)
            ->post('/user-update/' . $victim->id, [
                'name'      => 'Taken Over',
                'email'     => $victim->email,
                'user_role' => 'admin',
            ])
            ->assertForbidden();

        $this->assertSame('user', $victim->fresh()->user_role);

        // Editing their own record is fine, but the role must not move.
        $this->actingAs($this->employee)
            ->post('/user-update/' . $this->employee->id, [
                'name'      => 'My New Name',
                'email'     => $this->employee->email,
                'user_role' => 'admin',
            ]);

        $this->employee->refresh();
        $this->assertSame('My New Name', $this->employee->full_name);
        $this->assertSame('user', $this->employee->user_role);
    }

    /**
     * Posts exactly what the add-employee form sends, so a field the form is
     * missing (the password confirmation, say) fails here rather than in
     * someone's face.
     */
    public function test_admin_can_create_an_employee_through_the_form(): void
    {
        $shift = ShiftTiming::first() ?? ShiftTiming::create([
            'name' => 'Day shift', 'start_time' => '09:00', 'end_time' => '17:00', 'grace_period' => 15,
        ]);

        $this->actingAs($this->admin)
            ->post('/user-create', [
                'name'                  => 'Nadia Khan',
                'employee_code'         => 'EMP-0042',
                'email'                 => 'nadia@example.com',
                'phone'                 => '03001234567',
                'joining_date'          => '2026-01-15',
                'user_role'             => 'user',
                'password'              => 'Str0ng-Pass!23',
                'password_confirmation' => 'Str0ng-Pass!23',
                'department_id'         => Department::first()->id,
                'designation_id'        => Designation::first()->id,
                'shift_timing_id'       => $shift->id,
                'manager_id'            => $this->admin->id,
            ])
            ->assertRedirect(route('user-list'))
            ->assertSessionHas('status', 'success');

        $created = User::where('email', 'nadia@example.com')->firstOrFail();

        $this->assertSame('EMP-0042', $created->employee_code);
        $this->assertTrue($created->is_active);
        $this->assertNotNull($created->relation, 'the employee profile row should exist');
        $this->assertSame($shift->id, $created->relation->shift_timing_id);
    }

    /**
     * Saving the same profile twice used to insert a second relation row each
     * time, leaving duplicates that the shift lookup then picked from at random.
     */
    public function test_saving_a_profile_twice_keeps_one_relation_row(): void
    {
        $payload = [
            'name'            => $this->employee->full_name,
            'email'           => $this->employee->email,
            'user_role'       => 'user',
            'department_id'   => Department::first()->id,
            'designation_id'  => Designation::first()->id,
            'shift_timing_id' => ShiftTiming::first()->id,
            'manager_id'      => $this->admin->id,
            'is_active'       => 1,
        ];

        $this->actingAs($this->admin)->post('/user-update/' . $this->employee->id, $payload);
        $this->actingAs($this->admin)->post('/user-update/' . $this->employee->id, $payload);

        $this->assertSame(
            1,
            UserRelation::where('user_id', $this->employee->id)->count(),
        );
    }

    public function test_deactivated_user_cannot_log_in(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_check_in_then_check_out_records_worked_time(): void
    {
        $this->actingAs($this->employee);

        $this->post('/attendance/check-in')->assertRedirect();

        $record = Attendance::where('user_id', $this->employee->id)->firstOrFail();
        $this->assertNotNull($record->check_in);

        // A second punch must not open a new row or overwrite the first.
        $this->post('/attendance/check-in');
        $this->assertSame(1, Attendance::where('user_id', $this->employee->id)->count());

        $this->post('/attendance/check-out')->assertRedirect();

        $record->refresh();
        $this->assertNotNull($record->check_out);
        $this->assertNotNull($record->worked_minutes);
        $this->assertIsInt($record->worked_minutes);
    }

    public function test_check_out_without_check_in_is_rejected(): void
    {
        $this->actingAs($this->employee)
            ->post('/attendance/check-out')
            ->assertSessionHas('status', 'failed');
    }

    /**
     * The route existed but pointed at a method that was never written.
     */
    public function test_admin_can_correct_an_attendance_record(): void
    {
        $record = Attendance::create([
            'user_id'  => $this->employee->id,
            'date'     => now()->toDateString(),
            'check_in' => now()->setTime(10, 30),
            'status'   => 'late',
            'is_late'  => true,
        ]);

        $this->actingAs($this->admin)
            ->put('/attendance/' . $record->id, [
                'check_in'  => '09:05',
                'check_out' => '17:00',
            ])
            ->assertRedirect();

        $record->refresh();
        $this->assertFalse($record->is_late, 'inside the 15 minute grace window');
        $this->assertSame(475, $record->worked_minutes);
        $this->assertSame($this->admin->id, $record->edited_by);
    }

    public function test_leave_can_be_applied_for_and_approved(): void
    {
        $monday = now()->next('Monday');

        $this->actingAs($this->employee)
            ->post('/leave', [
                'type'       => 'casual',
                'start_date' => $monday->toDateString(),
                'end_date'   => $monday->copy()->addDay()->toDateString(),
                'reason'     => 'Family commitment',
            ])
            ->assertSessionHas('status', 'success');

        $leave = LeaveRequest::firstOrFail();
        $this->assertSame(2, (int) $leave->days);
        $this->assertSame('pending', $leave->status);

        // Overlapping dates must be refused.
        $this->actingAs($this->employee)
            ->post('/leave', [
                'type'       => 'sick',
                'start_date' => $monday->toDateString(),
                'end_date'   => $monday->toDateString(),
                'reason'     => 'Double booking',
            ])
            ->assertSessionHas('status', 'failed');

        $this->actingAs($this->admin)
            ->post('/admin/leave/' . $leave->id . '/status', [
                'status'      => 'approved',
                'admin_notes' => 'Approved',
            ])
            ->assertSessionHas('status', 'success');

        $this->assertSame('approved', $leave->fresh()->status);
    }

    /**
     * Pinned to a mid-month Wednesday: run on the 1st or 2nd of a month and
     * there are no past working days to report on at all.
     */
    public function test_weekly_offs_and_holidays_are_not_counted_absent(): void
    {
        $this->travelTo(Carbon::parse('2026-06-17 10:00:00'));

        // A Monday, so it does not land on a weekly off and get masked by it.
        OffDay::create(['title' => 'National Day', 'date' => '2026-06-01']);

        $report = app(\App\Services\AttendanceService::class)
            ->monthlyReport($this->employee, '2026-06');

        $statuses = array_column($report['days'], 'status');

        $this->assertContains('Weekly Off', $statuses);
        $this->assertContains('National Day', $statuses);
        $this->assertGreaterThan(0, $report['summary']['holiday']);
    }

    /**
     * The whole point of approving leave: those days stop counting against
     * the employee.
     */
    public function test_approved_leave_shows_as_leave_not_absence(): void
    {
        $this->travelTo(Carbon::parse('2026-06-17 10:00:00'));

        // A Wednesday already past, that nobody punched on.
        $day = Carbon::parse('2026-06-10');

        LeaveRequest::create([
            'user_id'    => $this->employee->id,
            'type'       => 'sick',
            'start_date' => $day->toDateString(),
            'end_date'   => $day->toDateString(),
            'days'       => 1,
            'reason'     => 'Unwell',
            'status'     => 'approved',
        ]);

        $report = app(\App\Services\AttendanceService::class)
            ->monthlyReport($this->employee, '2026-06');

        $row = collect($report['days'])->firstWhere('date', $day->toDateString());

        $this->assertNotNull($row, 'the leave day should appear in the month');
        $this->assertSame('Sick Leave', $row['status']);
        $this->assertSame(1, $report['summary']['leave']);
    }

    public function test_monthly_export_returns_a_csv(): void
    {
        $this->actingAs($this->admin)
            ->get('/monthly/export?month=' . now()->format('Y-m'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_a_bad_month_filter_does_not_blow_up(): void
    {
        $this->actingAs($this->employee)
            ->get('/attendance?month=not-a-month')
            ->assertOk();
    }
}
