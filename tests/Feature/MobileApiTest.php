<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\OffDay;
use App\Models\ShiftTiming;
use App\Models\User;
use App\Models\UserRelation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Contract the Android app relies on. If a field name here changes, the app
 * screen that reads it goes blank, so these assertions are deliberately
 * about shape as much as behaviour.
 */
class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

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
            'manager_id'      => User::factory()->admin()->create()->id,
        ]);
    }

    public function test_login_returns_a_token_and_the_user_shape(): void
    {
        $this->postJson('/api/login', [
            'email'    => $this->employee->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJson(['status' => true])
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'full_name', 'email', 'role', 'avatar_url', 'department', 'designation', 'shift'],
            ]);
    }

    public function test_login_rejects_bad_credentials_and_deactivated_accounts(): void
    {
        $this->postJson('/api/login', ['email' => $this->employee->email, 'password' => 'nope'])
            ->assertStatus(401);

        $inactive = User::factory()->inactive()->create();

        $this->postJson('/api/login', ['email' => $inactive->email, 'password' => 'password'])
            ->assertStatus(403);
    }

    public function test_protected_routes_need_a_token(): void
    {
        $this->getJson('/api/attendance/home')->assertStatus(401);
        $this->postJson('/api/attendance/check-in')->assertStatus(401);
    }

    public function test_home_payload_carries_everything_the_first_screen_needs(): void
    {
        Sanctum::actingAs($this->employee);

        $this->getJson('/api/attendance/home')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'server_time', 'business_date', 'can_check_in', 'can_check_out',
                    'is_off_day', 'ip_restricted', 'recent', 'leave_balance',
                    'shift'   => ['start', 'end', 'start_label', 'end_label', 'grace_period'],
                    'summary' => ['present', 'absent', 'late', 'attendance_pct'],
                ],
            ]);
    }

    public function test_check_in_and_check_out_round_trip(): void
    {
        Sanctum::actingAs($this->employee);

        $this->postJson('/api/attendance/check-in', ['latitude' => 24.86, 'longitude' => 67.01])
            ->assertOk()
            ->assertJson(['status' => true])
            ->assertJsonStructure(['data' => ['check_in_label', 'status', 'is_late']]);

        // Punching in twice must be refused rather than silently overwriting.
        $this->postJson('/api/attendance/check-in')->assertStatus(422);

        $this->postJson('/api/attendance/check-out', ['latitude' => 24.86, 'longitude' => 67.01])
            ->assertOk()
            ->assertJsonStructure(['data' => ['check_out_label', 'worked_minutes', 'worked_label']]);

        $this->postJson('/api/attendance/check-out')->assertStatus(422);

        $record = $this->employee->attendances()->firstOrFail();
        $this->assertSame('mobile', $record->source);
        $this->assertEqualsWithDelta(24.86, (float) $record->check_in_lat, 0.001);
    }

    public function test_check_out_before_check_in_is_refused(): void
    {
        Sanctum::actingAs($this->employee);

        $this->postJson('/api/attendance/check-out')
            ->assertStatus(422)
            ->assertJson(['status' => false]);
    }

    public function test_month_and_holidays_endpoints(): void
    {
        Sanctum::actingAs($this->employee);

        OffDay::create(['title' => 'National Day', 'date' => now()->startOfMonth()->toDateString()]);

        $this->getJson('/api/attendance/month?month=' . now()->format('Y-m'))
            ->assertOk()
            ->assertJsonStructure(['data' => ['month', 'label', 'days', 'summary']]);

        // A junk month must fall back, not explode.
        $this->getJson('/api/attendance/month?month=garbage')->assertOk();

        $this->getJson('/api/holidays?year=' . now()->year)
            ->assertOk()
            ->assertJsonFragment(['title' => 'National Day']);
    }

    public function test_leave_can_be_applied_for_and_cancelled(): void
    {
        Sanctum::actingAs($this->employee);

        $monday = now()->next('Monday');

        $response = $this->postJson('/api/leaves', [
            'type'       => 'casual',
            'start_date' => $monday->toDateString(),
            'end_date'   => $monday->copy()->addDay()->toDateString(),
            'reason'     => 'Family commitment',
        ])->assertStatus(201);

        $id = $response->json('data.id');
        $this->assertSame(2, $response->json('data.days'));

        // Overlapping request refused.
        $this->postJson('/api/leaves', [
            'type'       => 'sick',
            'start_date' => $monday->toDateString(),
            'end_date'   => $monday->toDateString(),
            'reason'     => 'Clash',
        ])->assertStatus(422);

        $this->getJson('/api/leaves')
            ->assertOk()
            ->assertJsonStructure(['data' => ['leaves', 'types', 'balance' => ['quota', 'used', 'remaining']]]);

        $this->postJson("/api/leaves/{$id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_one_employee_cannot_cancel_another_employees_leave(): void
    {
        $other = User::factory()->create();

        Sanctum::actingAs($other);
        $created = $this->postJson('/api/leaves', [
            'type'       => 'casual',
            'start_date' => now()->next('Monday')->toDateString(),
            'end_date'   => now()->next('Monday')->toDateString(),
            'reason'     => 'Theirs',
        ])->json('data.id');

        Sanctum::actingAs($this->employee);
        $this->postJson("/api/leaves/{$created}/cancel")->assertStatus(404);
    }

    public function test_profile_update_and_logout(): void
    {
        Sanctum::actingAs($this->employee);

        $this->postJson('/api/profile', ['full_name' => 'Updated Name', 'phone' => '03001234567'])
            ->assertOk()
            ->assertJsonPath('user.full_name', 'Updated Name');

        $this->postJson('/api/logout')->assertOk();
    }

    /**
     * The old endpoint returned the code in its own JSON response.
     */
    public function test_forgot_password_never_returns_the_otp(): void
    {
        $response = $this->postJson('/api/forgot-password', ['email' => $this->employee->email])->assertOk();

        $this->assertArrayNotHasKey('otp_for_testing', $response->json());
        $this->assertArrayNotHasKey('otp', $response->json());
        $this->assertNotNull($this->employee->fresh()->otp);

        // Unknown addresses get the same answer, so the endpoint cannot be
        // used to discover who has an account.
        $this->postJson('/api/forgot-password', ['email' => 'nobody@example.com'])
            ->assertOk()
            ->assertJson(['status' => true]);
    }

    /**
     * Accounts are created by an administrator. The self-service endpoint is
     * gone, so the route resolves to nothing (405, since the catch-all
     * fallback only answers GET) and no account appears either way.
     */
    public function test_public_registration_is_not_exposed(): void
    {
        $response = $this->postJson('/api/register', [
            'full_name' => 'Intruder',
            'email'     => 'intruder@example.com',
            'password'  => 'password',
        ]);

        $this->assertContains($response->status(), [404, 405]);
        $this->assertDatabaseMissing('users', ['email' => 'intruder@example.com']);
    }
}
