<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_but_cannot_access_dashboard(): void
    {
        $this->get('/admin/login')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->post('/admin/logout')->assertRedirect('/admin/login');
    }

    public function test_admin_can_login_with_normalized_email_and_session_is_rotated(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com', 'is_admin' => true]);
        $this->get('/admin/login');
        $previousId = session()->getId();

        $this->post('/admin/login', ['email' => ' ADMIN@EXAMPLE.COM ', 'password' => 'password'])
            ->assertSessionHasNoErrors()->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
        $this->assertNotSame($previousId, session()->getId());
        $this->get('/admin')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')->where('admin.email', $admin->email)->missing('admin.password'));
        $this->get('/admin/login')->assertRedirect('/admin');
    }

    public function test_wrong_password_does_not_authenticate_or_flash_password(): void
    {
        User::factory()->create(['email' => 'admin@example.com', 'is_admin' => true]);
        $this->from('/admin/login')->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'wrong-password'])
            ->assertRedirect('/admin/login')->assertSessionHasErrors('email')->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    public function test_regular_account_cannot_login_as_admin_or_bypass_dashboard_authorization(): void
    {
        $user = User::factory()->create();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password', 'is_admin' => true])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_revoking_admin_permission_blocks_an_existing_session(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin')->assertOk();
        $admin->is_admin = false;
        $admin->save();
        $this->get('/admin')->assertForbidden();
    }

    public function test_login_validates_required_fields_in_indonesian(): void
    {
        $this->post('/admin/login', [])->assertSessionHasErrors([
            'email' => 'Masukkan alamat email Anda.',
            'password' => 'Masukkan kata sandi Anda.',
        ]);
        $this->assertGuest();
    }

    public function test_login_is_temporarily_blocked_after_repeated_failures_and_recovers(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => $admin->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak percobaan', session('errors')->first('email'));
        $this->assertGuest();
        $this->travel(61)->seconds();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_logout_invalidates_session_and_removes_access(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password']);
        session()->put('private_marker', 'must-be-removed');
        $oldToken = session()->token();
        $this->post('/admin/logout')->assertRedirect('/admin/login')->assertSessionMissing('private_marker');
        $this->assertGuest();
        $this->assertNotSame($oldToken, session()->token());
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_flag_is_not_mass_assignable_and_public_registration_is_unavailable(): void
    {
        $user = new User(['name' => 'Example', 'email' => 'user@example.com', 'password' => 'password', 'is_admin' => true]);
        $this->assertNotTrue($user->is_admin);
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_create_admin_command_hashes_password_and_grants_access(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Nama Admin', 'Admin Pengujian')
            ->expectsQuestion('Email Admin', ' ADMIN@EXAMPLE.COM ')
            ->expectsQuestion('Kata sandi (minimal 12 karakter, maksimal 72 byte)', 'a-long-test-password')
            ->expectsQuestion('Ulangi kata sandi', 'a-long-test-password')
            ->assertSuccessful();
        $admin = User::where('email', 'admin@example.com')->sole();
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('a-long-test-password', $admin->password));
    }

    public function test_create_admin_command_does_not_promote_or_overwrite_existing_user(): void
    {
        $user = User::factory()->create(['email' => 'existing@example.com']);
        $previousPassword = $user->password;
        $this->artisan('admin:create')
            ->expectsQuestion('Nama Admin', 'Pengganti')
            ->expectsQuestion('Email Admin', $user->email)->assertFailed();
        $this->assertFalse($user->fresh()->is_admin);
        $this->assertSame($previousPassword, $user->fresh()->password);
    }

    public function test_create_admin_command_rejects_invalid_password_confirmation(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Nama Admin', 'Admin Pengujian')
            ->expectsQuestion('Email Admin', 'admin@example.com')
            ->expectsQuestion('Kata sandi (minimal 12 karakter, maksimal 72 byte)', 'a-long-test-password')
            ->expectsQuestion('Ulangi kata sandi', 'different-password')->assertFailed();
        $this->assertDatabaseCount('users', 0);
    }
}
