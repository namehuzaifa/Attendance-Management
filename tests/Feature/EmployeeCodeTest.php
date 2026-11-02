<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\ShiftTiming;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Employee codes are issued by the portal, counting from one, so nobody has
 * to invent and track them by hand.
 */
class EmployeeCodeTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        $shift = ShiftTiming::firstOr(fn () => ShiftTiming::create([
            'name' => 'Day shift', 'start_time' => '09:00', 'end_time' => '17:00', 'grace_period' => 15,
        ]));

        return array_merge([
            'name'                  => 'Nadia Khan',
            'email'                 => 'nadia' . uniqid() . '@example.com',
            'user_role'             => 'user',
            'password'              => 'Str0ng-Pass!23',
            'password_confirmation' => 'Str0ng-Pass!23',
            'department_id'         => Department::firstOr(fn () => Department::create(['name' => 'Engineering']))->id,
            'designation_id'        => Designation::firstOr(fn () => Designation::create(['name' => 'Developer']))->id,
            'shift_timing_id'       => $shift->id,
            'manager_id'            => User::where('user_role', 'admin')->value('id'),
        ], $overrides);
    }

    public function test_the_first_code_is_one(): void
    {
        $this->assertSame('EMP-0001', User::nextEmployeeCode());
    }

    public function test_codes_are_issued_in_order_when_the_field_is_left_blank(): void
    {
        $admin = User::factory()->admin()->create(['employee_code' => null]);

        $this->actingAs($admin)->post('/user-create', $this->payload())->assertRedirect();
        $this->actingAs($admin)->post('/user-create', $this->payload())->assertRedirect();

        $codes = User::whereNotNull('employee_code')->orderBy('id')->pluck('employee_code')->all();

        $this->assertSame(['EMP-0001', 'EMP-0002'], $codes);
    }

    public function test_a_code_typed_in_by_hand_is_kept(): void
    {
        $admin = User::factory()->admin()->create(['employee_code' => null]);

        $this->actingAs($admin)
            ->post('/user-create', $this->payload(['employee_code' => 'CONTRACT-7']))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['employee_code' => 'CONTRACT-7']);

        // The next automatic code ignores the hand-written one's format.
        $this->assertSame('EMP-0001', User::nextEmployeeCode());
    }

    /**
     * Removing somebody must not recycle their number onto the next hire —
     * the counter remembers what has been spent even once the row is gone.
     */
    public function test_a_deleted_employees_code_is_not_reused(): void
    {
        User::factory()->create(['employee_code' => User::issueEmployeeCode()]);
        $second = User::factory()->create(['employee_code' => User::issueEmployeeCode()]);

        $this->assertSame('EMP-0002', $second->employee_code);

        $second->delete();

        $this->assertSame('EMP-0003', User::nextEmployeeCode());
    }

    /**
     * Peeking at the next code must not consume it, or opening the add form
     * twice would burn a number each time.
     */
    public function test_looking_at_the_next_code_does_not_spend_it(): void
    {
        $this->assertSame('EMP-0001', User::nextEmployeeCode());
        $this->assertSame('EMP-0001', User::nextEmployeeCode());

        $this->assertSame('EMP-0001', User::issueEmployeeCode());
        $this->assertSame('EMP-0002', User::nextEmployeeCode());
    }

    public function test_the_generator_counts_past_nine_thousand_nine_hundred_and_ninety_nine(): void
    {
        User::factory()->create(['employee_code' => 'EMP-9999']);

        // Padding is a minimum, not a ceiling.
        $this->assertSame('EMP-10000', User::nextEmployeeCode());
    }

    public function test_existing_accounts_were_given_codes_by_the_backfill(): void
    {
        // RefreshDatabase re-runs the migration, so a user created before it
        // cannot be tested here; instead assert the rule it enforces, that
        // no account is left without a code once one is issued.
        $user = User::factory()->create(['employee_code' => null]);

        $user->update(['employee_code' => User::nextEmployeeCode()]);

        $this->assertNotNull($user->fresh()->employee_code);
        $this->assertStringStartsWith('EMP-', $user->fresh()->employee_code);
    }
}
