<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AdminNavigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SuperadminAccessTest extends TestCase
{
    use RefreshDatabase;

    private function root(): User
    {
        $root = User::factory()->create(['is_admin' => true, 'is_superadmin' => true, 'permissions' => []]);
        $this->actingAs($root);

        return $root;
    }

    private function access(User $user, array $changes = []): array
    {
        return [...$user->only('is_admin', 'is_superadmin', 'permissions', 'access_revision'), ...$changes];
    }

    public function test_unchanged_access_and_menus_do_not_increment_or_audit(): void
    {
        $root = $this->root();
        $count = DB::table('admin_audit_logs')->count();
        $this->put('/admin/settings/users/'.$root->id, $this->access($root))->assertSessionHasNoErrors();
        $this->assertSame($root->access_revision, $root->fresh()->access_revision);
        $menus = DB::table('admin_menu_items')->orderBy('key')->get()->map(fn ($row) => (array) $row)->all();
        $this->put('/admin/settings/menus', ['menus' => $menus])->assertSessionHasNoErrors();
        $this->assertSame($menus, DB::table('admin_menu_items')->orderBy('key')->get()->map(fn ($row) => (array) $row)->all());
        $this->assertSame($count, DB::table('admin_audit_logs')->count());
    }

    public function test_ordinary_admin_cannot_read_or_modify_superadmin_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin/settings/users')->assertForbidden();
        $this->get('/admin/settings/menus')->assertForbidden();
        $this->putJson('/admin/settings/users/'.$admin->id, $this->access($admin, ['is_superadmin' => true]))->assertForbidden();
        $this->putJson('/admin/settings/menus', ['menus' => []])->assertForbidden();
        $this->assertFalse($admin->fresh()->is_superadmin);
        $this->assertSame(0, DB::table('admin_audit_logs')->where('action', '!=', 'request.rejected')->count());
    }

    public function test_root_can_assign_access_and_changes_are_audited_with_conflict_protection(): void
    {
        $root = $this->root();
        $admin = User::factory()->create(['is_admin' => true, 'permissions' => []]);
        $payload = $this->access($admin, ['permissions' => ['programs.view']]);
        $this->get('/admin/settings/users')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Admin/Settings/Users')->has('users.data', 2));
        $this->putJson('/admin/settings/users/'.$admin->id, $payload)->assertRedirect();
        $this->assertSame(['programs.view'], $admin->fresh()->permissions);
        $this->assertDatabaseHas('admin_audit_logs', ['actor_id' => $root->id, 'target_user_id' => $admin->id, 'action' => 'user.access.updated']);
        $this->putJson('/admin/settings/users/'.$admin->id, $payload)->assertUnprocessable()->assertJsonValidationErrors('access_revision');
    }

    public function test_permissions_are_enforced_on_server_and_revocation_affects_existing_session(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'permissions' => ['programs.view']]);
        $this->actingAs($admin)->get('/admin/programs')->assertOk();
        $this->get('/admin/programs/create')->assertForbidden();
        $this->postJson('/admin/programs', [])->assertForbidden();
        $admin->permissions = [];
        $admin->save();
        $this->get('/admin/programs')->assertForbidden();
        $this->get('/admin')->assertOk()->assertInertia(fn (Assert $page) => $page->where('statistics', null)->has('recentPrograms', 0));
    }

    public function test_manage_permission_can_save_draft_but_cannot_publish(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'permissions' => ['programs.view', 'programs.manage']]);
        $this->actingAs($admin);
        $payload = ['client_id' => (string) Str::uuid(), 'action' => 'publish', 'revision' => 0, 'content' => ['name' => 'Test', 'category' => 'Test', 'description' => '', 'subprograms' => []]];
        $this->postJson('/admin/programs', $payload)->assertForbidden();
        $payload['action'] = 'draft';
        $this->postJson('/admin/programs', $payload)->assertOk();
    }

    public function test_root_has_program_access_without_individual_grants_and_cannot_revoke_self(): void
    {
        $root = $this->root();
        $this->get('/admin/programs/create')->assertOk();
        $this->putJson('/admin/settings/users/'.$root->id, $this->access($root, ['is_superadmin' => false]))
            ->assertUnprocessable()->assertJsonValidationErrors('is_superadmin');
        $this->assertTrue($root->fresh()->is_superadmin);
    }

    public function test_unknown_permissions_and_invalid_dependencies_are_rejected(): void
    {
        $this->root();
        $admin = User::factory()->create(['is_admin' => true, 'permissions' => []]);
        foreach ([['manage-access'], ['programs.publish'], ['programs.manage']] as $permissions) {
            $this->putJson('/admin/settings/users/'.$admin->id, $this->access($admin, compact('permissions')))->assertUnprocessable();
        }
        $this->assertSame([], $admin->fresh()->permissions);
    }

    public function test_hidden_menu_changes_navigation_but_does_not_grant_or_revoke_route_permission(): void
    {
        $this->root();
        $menus = DB::table('admin_menu_items')->orderBy('position')->get()->map(fn ($row) => (array) $row)->all();
        foreach ($menus as &$menu) {
            if ($menu['key'] === 'programs') {
                $menu['is_visible'] = false;
                $menu['label'] = 'Katalog Akademik';
            }
        }
        unset($menu);
        $this->putJson('/admin/settings/menus', compact('menus'))->assertRedirect();
        $admin = User::factory()->create(['is_admin' => true, 'permissions' => ['programs.view']]);
        $this->assertNotContains('programs', array_column(AdminNavigation::forUser($admin), 'key'));
        $this->actingAs($admin)->get('/admin/programs')->assertOk();
        DB::table('admin_menu_items')->where('key', 'programs')->update(['is_visible' => true]);
        $admin->permissions = [];
        $admin->save();
        $this->assertNotContains('programs', array_column(AdminNavigation::forUser($admin), 'key'));
        $this->get('/admin/programs')->assertForbidden();
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'menus.updated']);
    }

    public function test_core_menus_cannot_be_hidden_and_arbitrary_links_are_rejected(): void
    {
        $this->root();
        $menus = DB::table('admin_menu_items')->orderBy('position')->get()->map(fn ($row) => (array) $row)->all();
        $menus[0]['is_visible'] = false;
        $this->putJson('/admin/settings/menus', compact('menus'))->assertUnprocessable()->assertJsonValidationErrors('menus.0.is_visible');
        $menus[0]['is_visible'] = true;
        $menus[0]['path'] = 'javascript:alert(1)';
        $this->putJson('/admin/settings/menus', compact('menus'))->assertUnprocessable();
        $this->assertSame(0, DB::table('admin_audit_logs')->where('action', '!=', 'request.rejected')->count());
    }

    public function test_security_fields_cannot_be_mass_assigned_and_cli_promotes_only_selected_admin(): void
    {
        $user = new User(['name' => 'Test', 'email' => 'test@example.com', 'password' => 'password', 'is_superadmin' => true, 'permissions' => ['programs.publish']]);
        $this->assertNotTrue($user->is_superadmin);
        $this->assertSame([], $user->permissions);
        $admin = User::factory()->create(['is_admin' => true, 'permissions' => []]);
        $other = User::factory()->create(['is_admin' => true]);
        $this->artisan('admin:superadmin', ['email' => $admin->email])->assertSuccessful();
        $this->assertTrue($admin->fresh()->is_superadmin);
        $this->assertFalse($other->fresh()->is_superadmin);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'superadmin.granted.cli', 'target_user_id' => $admin->id]);
    }

    public function test_failed_access_audit_rolls_back_permission_change(): void
    {
        $this->root();
        $target = User::factory()->create(['is_admin' => true, 'permissions' => []]);
        DB::statement("ALTER TABLE admin_audit_logs ADD CONSTRAINT test_reject_access_audit CHECK (action <> 'user.access.updated')");
        $this->putJson('/admin/settings/users/'.$target->id, $this->access($target, ['permissions' => ['programs.view']]))->assertServerError();
        $this->assertSame([], $target->fresh()->permissions);
        $this->assertSame(1, $target->fresh()->access_revision);
        $this->assertSame(0, DB::table('admin_audit_logs')->where('action', '!=', 'request.rejected')->count());
    }

    public function test_stale_menu_update_is_rejected_and_non_admin_cannot_be_promoted_by_cli(): void
    {
        $this->root();
        $menus = DB::table('admin_menu_items')->orderBy('position')->get()->map(fn ($row) => (array) $row)->all();
        $menus[1]['label'] = 'Katalog pengujian';
        $this->putJson('/admin/settings/menus', compact('menus'))->assertRedirect();
        $menus[1]['label'] = 'Perubahan dari tab lama';
        $this->putJson('/admin/settings/menus', compact('menus'))->assertUnprocessable()->assertJsonValidationErrors('menus');
        $this->assertSame('Katalog pengujian', DB::table('admin_menu_items')->where('key', 'programs')->value('label'));
        $user = User::factory()->create();
        $this->artisan('admin:superadmin', ['email' => $user->email])->assertFailed();
        $this->assertFalse($user->fresh()->is_superadmin);
    }
}
