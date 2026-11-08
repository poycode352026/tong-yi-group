<?php

namespace Tests\Feature;

use App\Models\AcademicProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProgramManagementTest extends TestCase
{
    use RefreshDatabase;

    private function content(): array
    {
        $sessions = fn () => array_map(fn () => ['id' => (string) Str::uuid(), 'title' => 'Materi contoh', 'objectives' => 'Tujuan contoh', 'material' => 'Materi tersimpan', 'exercise' => '', 'reference' => ''], range(1, 8));
        $subprogram = fn ($name, $variant, $delivery, $service) => [
            'id' => (string) Str::uuid(), 'name' => $name, 'audience' => 'Dewasa', 'variant' => $variant,
            'levels' => [[
                'id' => (string) Str::uuid(), 'name' => 'Tahap contoh', 'package_count' => 3,
                'services' => [['id' => (string) Str::uuid(), 'name' => $service, 'delivery' => $delivery, 'price' => '250000', 'minimum' => 1, 'maximum' => 4]],
                'packages' => array_map(fn () => ['id' => (string) Str::uuid(), 'theme' => 'Tema contoh', 'sessions' => $sessions()], range(1, 3)),
            ]],
        ];

        return ['name' => 'Mandarin', 'category' => 'Bahasa', 'description' => 'Data pengujian, bukan katalog resmi', 'subprograms' => [
            $subprogram('Mandarin Sederhana', 'Sederhana', 'online', 'Group'),
            $subprogram('Mandarin Tradisional', 'Tradisional', 'offline', 'Private'),
        ]];
    }

    private function payload(?array $content = null, string $action = 'draft', int $revision = 0): array
    {
        return ['client_id' => (string) Str::uuid(), 'content' => $content ?? $this->content(), 'action' => $action, 'revision' => $revision];
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        return $admin;
    }

    public function test_unchanged_saves_skip_revisions_but_first_publication_still_counts(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true, 'is_superadmin' => true]));
        $payload = $this->payload();
        $id = $this->postJson('/admin/programs', $payload)->assertSuccessful()->json('id');
        $payload['revision'] = 1;
        $logs = DB::table('admin_audit_logs')->count();
        $this->putJson('/admin/programs/'.$id, $payload)->assertOk()->assertJsonPath('revision', 1);
        $this->assertSame($logs, DB::table('admin_audit_logs')->count());
        $payload['action'] = 'publish';
        $this->putJson('/admin/programs/'.$id, $payload)->assertOk()->assertJsonPath('revision', 2);
        $payload['revision'] = 2;
        $publishedAt = AcademicProgram::findOrFail($id)->published_at;
        $this->putJson('/admin/programs/'.$id, $payload)->assertOk()->assertJsonPath('revision', 2);
        $this->assertTrue($publishedAt->equalTo(AcademicProgram::findOrFail($id)->published_at));
        $this->assertSame(2, DB::table('program_revisions')->where('program_id', $id)->count());
        $payload['content']['description'] = 'Isi berubah';
        $this->putJson('/admin/programs/'.$id, $payload)->assertOk()->assertJsonPath('revision', 3);
    }

    public function test_program_routes_require_admin_on_read_and_write(): void
    {
        $this->get('/admin/programs')->assertRedirect('/admin/login');
        $this->postJson('/admin/programs', $this->payload())->assertUnauthorized();
        $this->actingAs(User::factory()->create())->get('/admin/programs/create')->assertForbidden();
        $this->postJson('/admin/programs', $this->payload())->assertForbidden();
        $this->assertDatabaseCount('academic_programs', 0);
    }

    public function test_program_list_and_full_page_form_render(): void
    {
        $this->admin();
        $this->get('/admin/programs')->assertInertia(fn (Assert $page) => $page->component('Admin/Programs/Index')->has('programs.data', 0));
        $this->get('/admin/programs/create')->assertInertia(fn (Assert $page) => $page->component('Admin/Programs/Edit')->where('program', null));
    }

    public function test_flexible_services_and_twenty_four_sessions_persist_with_audit(): void
    {
        $admin = $this->admin();
        $response = $this->postJson('/admin/programs', $this->payload(action: 'publish'))->assertOk();
        $program = AcademicProgram::findOrFail($response->json('id'));
        $this->assertNotNull($program->published);
        $simplified = $program->published['subprograms'][0]['levels'][0];
        $traditional = $program->published['subprograms'][1]['levels'][0];
        $this->assertCount(1, $simplified['services']);
        $this->assertSame('online', $simplified['services'][0]['delivery']);
        $this->assertSame('offline', $traditional['services'][0]['delivery']);
        $this->assertSame('Private', $traditional['services'][0]['name']);
        $this->assertSame(24, array_sum(array_map(fn ($p) => count($p['sessions']), $simplified['packages'])));
        $this->assertSame('Materi tersimpan', $simplified['packages'][2]['sessions'][7]['material']);
        $this->assertDatabaseHas('program_revisions', ['program_id' => $program->id, 'revision' => 1, 'action' => 'publish', 'actor_id' => $admin->id]);
    }

    public function test_non_language_program_and_incomplete_draft_can_be_saved_and_resumed(): void
    {
        $this->admin();
        $content = ['name' => 'Program pengujian nonbahasa', 'category' => 'Kategori pengujian', 'description' => '', 'subprograms' => []];
        $result = $this->postJson('/admin/programs', $this->payload($content))->assertOk();
        $this->assertNull(AcademicProgram::find($result->json('id'))->published);
        $this->get('/admin/programs/'.$result->json('id').'/edit')->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Programs/Edit')->where('program.content.name', $content['name'])->where('program.content.category', $content['category']));
        $this->postJson('/admin/programs', $this->payload(['name' => '', 'category' => '', 'description' => '', 'subprograms' => []]))->assertOk();
    }

    public function test_changes_remain_draft_and_variants_are_independent(): void
    {
        $this->admin();
        $id = $this->postJson('/admin/programs', $this->payload(action: 'publish'))->json('id');
        $program = AcademicProgram::find($id);
        $previous = $program->published;
        $changed = $previous;
        $changed['subprograms'][0]['levels'][0]['services'][0]['price'] = '500000';
        $changed['subprograms'][0]['levels'][0]['packages'][0]['sessions'][0]['material'] = 'Materi baru';
        $this->putJson('/admin/programs/'.$id, $this->payload($changed, revision: 1))->assertOk();
        $program->refresh();
        $this->assertEquals($previous, $program->published);
        $this->assertEquals($previous['subprograms'][1], $program->draft['subprograms'][1]);
        $this->assertSame('500000', $program->draft['subprograms'][0]['levels'][0]['services'][0]['price']);
        $this->putJson('/admin/programs/'.$id, $this->payload($changed, 'publish', 2))->assertOk();
        $this->assertEquals($changed, $program->fresh()->published);
        $this->assertDatabaseCount('program_revisions', 3);
    }

    public function test_invalid_capacity_is_rejected_without_changing_saved_data(): void
    {
        $this->admin();
        $id = $this->postJson('/admin/programs', $this->payload())->json('id');
        $program = AcademicProgram::find($id);
        $changed = $program->draft;
        $changed['subprograms'][0]['levels'][0]['services'][0]['minimum'] = 5;
        $changed['subprograms'][0]['levels'][0]['services'][0]['maximum'] = 2;
        $this->putJson('/admin/programs/'.$id, $this->payload($changed, revision: 1))->assertUnprocessable()
            ->assertJsonValidationErrors('content.subprograms.0.levels.0.services.0.maximum');
        $this->assertEquals($program->draft, $program->fresh()->draft);
        $this->assertDatabaseCount('program_revisions', 1);
    }

    public function test_reducing_active_packages_preserves_material_and_rejects_omission(): void
    {
        $this->admin();
        $id = $this->postJson('/admin/programs', $this->payload())->json('id');
        $content = AcademicProgram::find($id)->draft;
        $content['subprograms'][0]['levels'][0]['package_count'] = 1;
        $this->putJson('/admin/programs/'.$id, $this->payload($content, revision: 1))->assertOk();
        $this->assertCount(3, AcademicProgram::find($id)->draft['subprograms'][0]['levels'][0]['packages']);
        $content['subprograms'][0]['levels'][0]['packages'] = [$content['subprograms'][0]['levels'][0]['packages'][0]];
        $this->putJson('/admin/programs/'.$id, $this->payload($content, revision: 2))->assertUnprocessable()->assertJsonValidationErrors('content');
        $this->assertDatabaseCount('program_revisions', 2);
    }

    public function test_stale_tab_cannot_overwrite_more_recent_edits(): void
    {
        $this->admin();
        $id = $this->postJson('/admin/programs', $this->payload())->json('id');
        $content = AcademicProgram::find($id)->draft;
        $content['name'] = 'Perubahan pertama';
        $this->putJson('/admin/programs/'.$id, $this->payload($content, revision: 1))->assertOk();
        $content['name'] = 'Perubahan tab lama';
        $this->putJson('/admin/programs/'.$id, $this->payload($content, revision: 1))->assertConflict();
        $this->assertSame('Perubahan pertama', AcademicProgram::find($id)->draft['name']);
        $this->assertDatabaseCount('program_revisions', 2);
    }

    public function test_duplicate_save_retry_does_not_create_a_second_program(): void
    {
        $this->admin();
        $payload = $this->payload();
        $first = $this->postJson('/admin/programs', $payload)->assertOk()->json('id');
        $this->postJson('/admin/programs', $payload)->assertOk()->assertJsonPath('id', $first);
        $this->assertDatabaseCount('academic_programs', 1);
        $this->assertDatabaseCount('program_revisions', 1);
    }

    public function test_duplicate_node_ids_and_incomplete_session_arrays_are_rejected(): void
    {
        $this->admin();
        $content = $this->content();
        $content['subprograms'][1]['id'] = $content['subprograms'][0]['id'];
        $this->postJson('/admin/programs', $this->payload($content))->assertUnprocessable()->assertJsonValidationErrors('content');
        $content = $this->content();
        array_pop($content['subprograms'][0]['levels'][0]['packages'][0]['sessions']);
        $this->postJson('/admin/programs', $this->payload($content))->assertUnprocessable()->assertJsonValidationErrors('content.subprograms.0.levels.0.packages.0.sessions');
        $this->assertDatabaseCount('academic_programs', 0);
    }

    public function test_snapshot_failure_rolls_back_entire_program_write(): void
    {
        $this->admin();
        // Constraint sementara hanya di transaksi database tes, untuk mensimulasikan kegagalan audit.
        DB::statement("ALTER TABLE program_revisions ADD CONSTRAINT test_reject_snapshot CHECK (action <> 'publish')");
        $this->postJson('/admin/programs', $this->payload(action: 'publish'))->assertServerError();
        $this->assertDatabaseCount('academic_programs', 0);
        $this->assertDatabaseCount('program_revisions', 0);
    }

    public function test_dashboard_counts_real_programs_and_keeps_published_programs_with_draft_changes_published(): void
    {
        $this->admin();
        $content = ['name' => 'Program pengujian', 'category' => 'Kategori pengujian', 'description' => '', 'subprograms' => []];
        $published = $this->postJson('/admin/programs', $this->payload($content, 'publish'))->assertOk()->json('id');
        $this->postJson('/admin/programs', $this->payload($content))->assertOk();
        $this->postJson('/admin/programs', $this->payload($content))->assertOk();
        $this->travel(1)->minutes();
        $content['name'] = 'Perubahan draf terbaru';
        $this->putJson('/admin/programs/'.$published, $this->payload($content, revision: 1))->assertOk();
        $this->get('/admin')->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->where('statistics.total', 3)
            ->where('statistics.drafts', 2)
            ->where('statistics.published', 1)
            ->has('recentPrograms', 3)
            ->where('recentPrograms.0.id', $published)
            ->where('recentPrograms.0.name', 'Perubahan draf terbaru')
            ->where('recentPrograms.0.has_changes', true)
            ->where('recentPrograms.0.published', true));
    }

    public function test_saved_level_can_be_archived_and_restored_without_losing_material_or_changing_published_version(): void
    {
        $this->admin();
        $id = $this->postJson('/admin/programs', $this->payload(action: 'publish'))->json('id');
        $previous = AcademicProgram::find($id)->published;
        $content = $previous;
        $level = array_shift($content['subprograms'][0]['levels']);
        $content['subprograms'][0]['archived_levels'] = [$level];
        $this->putJson('/admin/programs/'.$id, $this->payload($content, revision: 1))->assertOk();
        $program = AcademicProgram::find($id);
        $this->assertCount(0, $program->draft['subprograms'][0]['levels']);
        $this->assertEquals($level, $program->draft['subprograms'][0]['archived_levels'][0]);
        $this->assertEquals($previous, $program->published);
        $this->actingAs(User::factory()->create(['is_admin' => true, 'is_superadmin' => true]));
        $restored = $program->draft;
        $restored['subprograms'][0]['levels'][] = array_shift($restored['subprograms'][0]['archived_levels']);
        $this->putJson('/admin/programs/'.$id, $this->payload($restored, 'publish', 2))->assertOk();
        $this->assertEquals($level, $program->fresh()->published['subprograms'][0]['levels'][0]);
        $this->assertDatabaseCount('program_revisions', 3);
    }

    public function test_accidentally_added_blank_level_can_be_archived_and_does_not_block_publication(): void
    {
        $this->admin();
        $content = $this->content();
        $blank = ['id' => (string) Str::uuid(), 'name' => '', 'package_count' => 1, 'services' => [], 'packages' => [$content['subprograms'][0]['levels'][0]['packages'][0]]];
        $blank['packages'][0]['id'] = (string) Str::uuid();
        foreach ($blank['packages'][0]['sessions'] as &$session) {
            $session['id'] = (string) Str::uuid();
        }
        unset($session);
        $content['subprograms'][0]['levels'][] = $blank;
        $id = $this->postJson('/admin/programs', $this->payload($content))->assertOk()->json('id');
        $content = AcademicProgram::find($id)->draft;
        $content['subprograms'][0]['archived_levels'] = [array_pop($content['subprograms'][0]['levels'])];
        $this->putJson('/admin/programs/'.$id, $this->payload($content, 'publish', 1))->assertOk();
        $this->assertCount(1, AcademicProgram::find($id)->published['subprograms'][0]['levels']);
        $this->assertCount(1, AcademicProgram::find($id)->published['subprograms'][0]['archived_levels']);
    }

    public function test_saved_level_still_cannot_be_silently_discarded(): void
    {
        $this->admin();
        $id = $this->postJson('/admin/programs', $this->payload())->assertOk()->json('id');
        $content = AcademicProgram::find($id)->draft;
        array_pop($content['subprograms'][0]['levels']);
        $this->putJson('/admin/programs/'.$id, $this->payload($content, revision: 1))
            ->assertUnprocessable()->assertJsonValidationErrors('content');
        $this->assertCount(1, AcademicProgram::find($id)->draft['subprograms'][0]['levels']);
    }

    public function test_canonical_price_preserves_decimal_value_and_invalid_amounts_are_rejected(): void
    {
        $this->admin();
        $content = $this->content();
        $content['subprograms'][0]['levels'][0]['services'][0]['price'] = '1250000.50';
        $id = $this->postJson('/admin/programs', $this->payload($content))->assertOk()->json('id');
        $this->assertSame('1250000.50', AcademicProgram::find($id)->draft['subprograms'][0]['levels'][0]['services'][0]['price']);
        foreach (['-100', '1.234', 'Rp 1.250.000,50'] as $invalid) {
            $content['subprograms'][0]['levels'][0]['services'][0]['price'] = $invalid;
            $this->putJson('/admin/programs/'.$id, $this->payload($content, revision: 1))
                ->assertUnprocessable()->assertJsonValidationErrors('content.subprograms.0.levels.0.services.0.price');
        }
    }
}
