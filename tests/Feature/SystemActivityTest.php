<?php

namespace Tests\Feature;

use App\Models\AcademicProgram;
use App\Models\User;
use App\Support\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SystemActivityTest extends TestCase
{
    use RefreshDatabase;

    private function root(): User
    {
        $user = User::factory()->create(['is_admin' => true, 'is_superadmin' => true]);
        $this->actingAs($user);

        return $user;
    }

    private function program(): AcademicProgram
    {
        $sessions = array_map(fn () => ['id' => (string) Str::uuid(), 'title' => '', 'objectives' => '', 'material' => 'Materi tetap disimpan', 'exercise' => '', 'reference' => ''], range(1, 8));
        $level = ['id' => (string) Str::uuid(), 'name' => 'Level Arsip Uji', 'package_count' => 1, 'services' => [], 'packages' => [['id' => (string) Str::uuid(), 'theme' => '', 'sessions' => $sessions]]];
        $payload = ['client_id' => (string) Str::uuid(), 'revision' => 0, 'action' => 'publish', 'content' => ['name' => 'Program Arsip Uji', 'category' => 'Uji', 'description' => '', 'subprograms' => [['id' => (string) Str::uuid(), 'name' => 'Subprogram Uji', 'audience' => '', 'variant' => '', 'levels' => [], 'archived_levels' => [$level]]]]];

        return AcademicProgram::findOrFail($this->postJson('/admin/programs', $payload)->assertOk()->json('id'));
    }

    public function test_only_superadmin_can_view_logs_and_archives_or_restore(): void
    {
        $this->root();
        $program = $this->program();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        foreach (['/admin/system/activity', '/admin/system/activity/1', '/admin/system/archives'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->postJson('/admin/system/archives/'.$program->id.'/restore', [])->assertForbidden();
        $next = $program->draft;
        $next['subprograms'][0]['levels'][] = array_shift($next['subprograms'][0]['archived_levels']);
        $this->putJson('/admin/programs/'.$program->id, ['action' => 'draft', 'revision' => 1, 'content' => $next])->assertUnprocessable();
        $changedArchive = $program->draft;
        $changedArchive['subprograms'][0]['archived_levels'][0]['name'] = 'Tidak boleh diubah';
        $this->putJson('/admin/programs/'.$program->id, ['action' => 'draft', 'revision' => 1, 'content' => $changedArchive])->assertUnprocessable();
        $this->assertCount(1, $program->fresh()->draft['subprograms'][0]['archived_levels']);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'request.rejected', 'outcome' => 'failed']);
    }

    public function test_archived_level_restore_is_revision_checked_audited_and_preserves_publication(): void
    {
        $root = $this->root();
        $program = $this->program();
        $before = $program->published;
        $sub = $before['subprograms'][0];
        $payload = ['revision' => 1, 'subprogram_id' => $sub['id'], 'level_id' => $sub['archived_levels'][0]['id']];
        $this->get('/admin/system/archives')->assertOk()->assertInertia(fn (Assert $page) => $page->has('archives.data', 1)->where('archives.data.0.level_name', 'Level Arsip Uji'));
        $this->post('/admin/system/archives/'.$program->id.'/restore', $payload)->assertRedirect()->assertSessionHasNoErrors();
        $program->refresh();
        $this->assertSame($before, $program->published);
        $this->assertCount(0, $program->draft['subprograms'][0]['archived_levels']);
        $this->assertSame('Materi tetap disimpan', $program->draft['subprograms'][0]['levels'][0]['packages'][0]['sessions'][0]['material']);
        $this->assertSame(2, $program->revision);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'level.restored', 'actor_id' => $root->id, 'subject_id' => $program->id]);
        $this->post('/admin/system/archives/'.$program->id.'/restore', $payload)->assertSessionHasErrors('revision');
        $this->assertSame(2, $program->fresh()->revision);
    }

    public function test_program_audit_and_data_roll_back_together_when_audit_fails(): void
    {
        $this->root();
        $program = $this->program();
        DB::statement("ALTER TABLE admin_audit_logs ADD CONSTRAINT test_reject_program_audit CHECK (action <> 'program.draft.saved')");
        $content = $program->draft;
        $content['description'] = 'Tidak boleh tersimpan';
        $this->putJson('/admin/programs/'.$program->id, ['action' => 'draft', 'revision' => 1, 'content' => $content])->assertServerError();
        $this->assertSame($program->draft['description'], $program->fresh()->draft['description']);
        $this->assertSame(1, $program->fresh()->revision);
        $this->assertSame(1, DB::table('program_revisions')->where('program_id', $program->id)->count());
    }

    public function test_log_filters_details_and_secret_redaction(): void
    {
        $root = $this->root();
        ActivityLog::record($root->id, 'test.change', 'Catatan uji filter', [], ['nested' => ['name' => 'Uji Aman', 'password' => 'JANGAN-BOCOR', 'access_token' => 'TOKEN-RAHASIA']]);
        $id = DB::table('admin_audit_logs')->where('action', 'test.change')->value('id');
        $this->assertStringNotContainsString('JANGAN-BOCOR', DB::table('admin_audit_logs')->where('id', $id)->value('after'));
        $this->get('/admin/system/activity?search=Catatan%20uji&action=test.change')->assertOk()->assertInertia(fn (Assert $page) => $page->has('logs.data', 1)->where('logs.data.0.actor', $root->name)->missing('logs.data.0.before')->missing('logs.data.0.after'));
        $this->get('/admin/system/activity/'.$id)->assertOk()->assertInertia(fn (Assert $page) => $page->has('changes', 1)->where('changes.0.after', 'Uji Aman'));
        $this->get('/admin/system/activity?to=2099-01-01')->assertOk();
        $this->get('/admin/system/activity?from=invalid')->assertSessionHasErrors('from');
    }
}
