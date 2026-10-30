<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * This file used to exercise Breeze's /profile routes, which pointed at a
 * ProfileController that was never written in this project. The portal edits
 * a profile through user-edit / user-update, so that is what is covered now.
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/user-edit/' . $user->id)
            ->assertOk()
            ->assertSee($user->email);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/user-update/' . $user->id, [
                'name'  => 'Zara Ahmed',
                'email' => 'zara@example.com',
                'phone' => '03009998877',
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertSame('Zara Ahmed', $user->full_name);
        $this->assertSame('zara@example.com', $user->email);
        $this->assertSame('03009998877', $user->phone);
    }

    public function test_password_can_be_changed_from_the_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/user-update/' . $user->id, [
                'name'                  => $user->full_name,
                'email'                 => $user->email,
                'password'              => 'Str0ng-NewPass!',
                'password_confirmation' => 'Str0ng-NewPass!',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('Str0ng-NewPass!', $user->fresh()->password));
    }

    public function test_an_unconfirmed_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/user-update/' . $user->id, [
                'name'                  => $user->full_name,
                'email'                 => $user->email,
                'password'              => 'Str0ng-NewPass!',
                'password_confirmation' => 'something-else',
            ])
            ->assertSessionHasErrors('password');

        $this->assertFalse(Hash::check('Str0ng-NewPass!', $user->fresh()->password));
    }

    public function test_email_must_stay_unique(): void
    {
        $taken = User::factory()->create();
        $user  = User::factory()->create();

        $this->actingAs($user)
            ->post('/user-update/' . $user->id, [
                'name'  => $user->full_name,
                'email' => $taken->email,
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_an_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete('/user-delete/' . $admin->id)
            ->assertSessionHas('status', 'failed');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_the_last_administrator_cannot_be_removed(): void
    {
        $admin  = User::factory()->admin()->create();
        $second = User::factory()->admin()->create();

        // Removing one of two is fine.
        $this->actingAs($admin)->delete('/user-delete/' . $second->id);
        $this->assertDatabaseMissing('users', ['id' => $second->id]);

        // The remaining one cannot be demoted away either.
        $this->actingAs($admin)
            ->post('/user-update/' . $admin->id, [
                'name'      => $admin->full_name,
                'email'     => $admin->email,
                'user_role' => 'user',
            ])
            ->assertSessionHas('status', 'failed');

        $this->assertSame('admin', $admin->fresh()->user_role);
    }
}
