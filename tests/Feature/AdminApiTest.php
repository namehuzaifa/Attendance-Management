<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\LeaveRequest;
use App\Models\ShiftTiming;
use App\Models\User;
use App\Models\UserRelation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The administrator half of the mobile API. These assertions pin the JSON
 * the admin screens read, and the gate that keeps employees out of it.
 */
class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->employee = User::factory()->create(['full_name' => 'Ayesha Siddiqui']);

        UserRelation::create([
            'user_id'         => $this->employee->id,
            'department_id'   => Department::create(['name' => 'Engineering'])->id,
            'designation_id'  => Designation::create(['name' => 'Developer'])->id,
            'shift_timing_id' => ShiftTiming::create([
                'name' => 'Day shift', 'start_time' => '09:00', 'end_time' => '17:00',
                'grace_period' => 15, 'off_days' => ['Saturday', 'Sunday'],
            ])->id,
            'manager_id'      => $this->admin->id,
        ]);
    }

    public function test_an_employee_is_refused_every_admin_route(): void
    {
        Sanctum::actingAs($this->employee);

        $this->getJson('/api/admin/dashboard')->assertStatus(403);
        $this->getJson('/api/admin/employees')->assertStatus(403);
        $this->getJson('/api/admin/leaves')->assertStatus(403);
        $this->getJson('/api/admin/attendance')->assertStatus(403);
    }

    public function test_admin_routes_need_a_token_at_all(): void
    {
        $this->getJson('/api/admin/dashboard')->assertStatus(401);
    }

    public function test_dashboard_reports_todays_turnout(): void
    {
        Attendance::create([
            'user_id'  => $this->employee->id,
            'date'     => now()->toDateString(),
            'check_in' => now()->setTime(9, 45),
            'status'   => 'late',
            'is_late'  => true,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'date', 'label', 'pending_leaves', 'recent_punches', 'missing', 'on_leave', 'trend',
                    'stats' => ['employees', 'present', 'late', 'checked_out', 'on_leave', 'missing', 'rate'],
                ],
            ]);

        $this->assertSame(1, $response->json('data.stats.present'));
        $this->assertSame(1, $response->json('data.stats.late'));
        $this->assertSame('Ayesha Siddiqui', $response->json('data.recent_punches.0.name'));
        $this->assertCount(14, $response->json('data.trend'));
    }

    public function test_employee_list_carries_todays_status_and_can_be_searched(): void
    {
        Attendance::create([
            'user_id'  => $this->employee->id,
            'date'     => now()->toDateString(),
            'check_in' => now()->setTime(9, 2),
            'status'   => 'on time',
        ]);

        Sanctum::actingAs($this->admin);

        $this->getJson('/api/admin/employees')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Ayesha Siddiqui')
            ->assertJsonPath('data.0.department', 'Engineering')
            ->assertJsonPath('data.0.today.is_late', false);

        $this->getJson('/api/admin/employees?q=Ayesha')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/admin/employees?q=nobody-by-this-name')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_employee_report_returns_a_month(): void
    {
        Sanctum::actingAs($this->admin);

        $this->getJson("/api/admin/employees/{$this->employee->id}/report")
            ->assertOk()
            ->assertJsonPath('data.employee.name', 'Ayesha Siddiqui')
            ->assertJsonStructure(['data' => ['month', 'label', 'days', 'summary' => ['present', 'absent', 'attendance_pct']]]);

        // A junk month falls back rather than throwing.
        $this->getJson("/api/admin/employees/{$this->employee->id}/report?month=garbage")->assertOk();
    }

    public function test_admin_can_approve_a_leave_request_once(): void
    {
        $leave = LeaveRequest::create([
            'user_id'    => $this->employee->id,
            'type'       => 'casual',
            'start_date' => now()->next('Monday')->toDateString(),
            'end_date'   => now()->next('Monday')->toDateString(),
            'days'       => 1,
            'reason'     => 'Errand',
            'status'     => 'pending',
        ]);

        Sanctum::actingAs($this->admin);

        $this->getJson('/api/admin/leaves')
            ->assertOk()
            ->assertJsonPath('data.counts.pending', 1)
            ->assertJsonPath('data.leaves.0.employee', 'Ayesha Siddiqui');

        $this->postJson("/api/admin/leaves/{$leave->id}/status", [
            'status'      => 'approved',
            'admin_notes' => 'Fine by me',
        ])->assertOk();

        $leave->refresh();
        $this->assertSame('approved', $leave->status);
        $this->assertSame($this->admin->id, $leave->approved_by);

        // Deciding twice must not silently overwrite the first decision.
        $this->postJson("/api/admin/leaves/{$leave->id}/status", ['status' => 'rejected'])
            ->assertStatus(422);

        $this->assertSame('approved', $leave->fresh()->status);
    }

    /**
     * The admin location view is built from these coordinates, so they have
     * to survive the round trip as numbers, and be null when not shared.
     */
    public function test_attendance_rows_carry_punch_coordinates(): void
    {
        Attendance::create([
            'user_id'       => $this->employee->id,
            'date'          => now()->toDateString(),
            'check_in'      => now()->setTime(9, 0),
            'check_out'     => now()->setTime(17, 0),
            'status'        => 'on time',
            'check_in_lat'  => 24.8607343,
            'check_in_lng'  => 67.0011364,
            'check_out_lat' => 24.8611000,
            'check_out_lng' => 67.0015000,
        ]);

        $withoutCoords = User::factory()->create();
        Attendance::create([
            'user_id'  => $withoutCoords->id,
            'date'     => now()->toDateString(),
            'check_in' => now()->setTime(9, 30),
            'status'   => 'on time',
        ]);

        Sanctum::actingAs($this->admin);

        $rows = collect(
            $this->getJson('/api/admin/attendance?date=' . now()->toDateString())
                ->assertOk()
                ->json('data.records')
        );

        $located = $rows->firstWhere('name', 'Ayesha Siddiqui');

        $this->assertEqualsWithDelta(24.8607343, $located['check_in_location']['lat'], 0.0000001);
        $this->assertEqualsWithDelta(67.0011364, $located['check_in_location']['lng'], 0.0000001);
        $this->assertNotNull($located['check_out_location']);

        $other = $rows->firstWhere('name', $withoutCoords->full_name);

        $this->assertNull($other['check_in_location'], 'a punch with no coordinates must report null');
        $this->assertNull($other['check_out_location']);
    }

    public function test_attendance_for_a_given_day(): void
    {
        Attendance::create([
            'user_id'   => $this->employee->id,
            'date'      => now()->toDateString(),
            'check_in'  => now()->setTime(9, 0),
            'check_out' => now()->setTime(17, 10),
            'status'    => 'on time',
        ]);

        Sanctum::actingAs($this->admin);

        $this->getJson('/api/admin/attendance?date=' . now()->toDateString())
            ->assertOk()
            ->assertJsonPath('data.records.0.name', 'Ayesha Siddiqui')
            ->assertJsonPath('data.records.0.check_in', '09:00 AM');

        $this->getJson('/api/admin/attendance?date=not-a-date')->assertOk();
    }
}
