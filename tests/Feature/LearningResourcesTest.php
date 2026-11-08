<?php

namespace Tests\Feature;

use App\Models\AcademicProgram;
use App\Models\LearningResource;
use App\Models\LearningResourceVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LearningResourcesTest extends TestCase
{
    use RefreshDatabase;

    private function root(): User
    {
        $user = User::factory()->create(['is_admin' => true, 'is_superadmin' => true]);
        $this->actingAs($user);

        return $user;
    }

    private ?AcademicProgram $examProgram = null;

    private function payload(string $kind, array $changes = []): array
    {
        $target = null;
        if ($kind === 'assessments') {
            if (! $this->examProgram) {
                $this->examProgram = new AcademicProgram;
                $this->examProgram->draft = $this->program()['content'];
                $this->examProgram->revision = 1;
                $this->examProgram->created_by = auth()->id();
                $this->examProgram->save();
            }
            $sub = $this->examProgram->draft['subprograms'][0];
            $level = $sub['levels'][0];
            $target = ['program_id' => $this->examProgram->id, 'program_revision' => 1, 'subprogram_id' => $sub['id'], 'level_id' => $level['id'], 'package_id' => $level['packages'][0]['id']];
        }

        return array_replace(['revision' => 0, 'title' => 'Data pengujian', 'status' => 'ready', ...match ($kind) {
            'frameworks' => ['levels' => ['Tingkat Dasar', 'Tingkat Lanjut']],
            'materials' => ['source' => 'link', 'url' => 'https://example.org/reference'],
            'assessments' => ['target' => $target, 'scope' => 'package', 'questions' => [['id' => (string) Str::uuid(), 'type' => 'choice', 'prompt' => 'Pertanyaan uji', 'options' => ['A', 'B'], 'answer' => 0, 'guide' => 'Penjelasan rahasia']]],
        }], $changes);
    }

    private function createVersion(string $kind, array $changes = []): LearningResourceVersion
    {
        $beforeIds = LearningResourceVersion::pluck('id')->all();
        $before = count($beforeIds);
        $this->post('/admin/learning/'.$kind, $this->payload($kind, $changes))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($before + 1, LearningResourceVersion::count());

        return LearningResourceVersion::whereNotIn('id', $beforeIds)->firstOrFail();
    }

    private function program(array $levelChanges = []): array
    {
        $sessions = array_map(fn () => ['id' => (string) Str::uuid(), 'title' => '', 'objectives' => '', 'material' => '', 'exercise' => 'Arahan guru', 'reference' => ''], range(1, 8));
        $level = ['id' => (string) Str::uuid(), 'name' => 'Dasar', 'package_count' => 1, 'services' => [], 'packages' => [['id' => (string) Str::uuid(), 'theme' => '', 'sessions' => $sessions]], ...$levelChanges];

        return ['client_id' => (string) Str::uuid(), 'revision' => 0, 'action' => 'publish', 'content' => ['name' => 'Program Uji', 'category' => 'Kategori', 'description' => '', 'subprograms' => [['id' => (string) Str::uuid(), 'name' => 'Subprogram Uji', 'audience' => '', 'variant' => '', 'levels' => [$level]]]]];
    }

    public function test_repeated_unchanged_resources_do_not_create_versions_or_activity(): void
    {
        $this->root();
        foreach (['frameworks', 'materials', 'assessments'] as $kind) {
            $payload = $this->payload($kind);
            $this->post('/admin/learning/'.$kind, $payload)->assertSessionHasNoErrors();
            $resource = LearningResource::where('kind', $kind)->firstOrFail();
            $payload['revision'] = 1;
            $payload['description'] = '';
            $logs = DB::table('admin_audit_logs')->count();
            for ($i = 0; $i < 2; $i++) {
                $this->post('/admin/learning/'.$kind.'/'.$resource->id, $payload)->assertSessionHasNoErrors()->assertSessionHas('success', 'Tidak ada perubahan. Versi tetap sama.');
            }
            $this->assertSame(1, $resource->fresh()->revision);
            $this->assertSame(1, $resource->versions()->count());
            $this->assertSame($logs, DB::table('admin_audit_logs')->count());
            $payload['description'] = 'Perubahan nyata';
            $this->post('/admin/learning/'.$kind.'/'.$resource->id, $payload)->assertSessionHasNoErrors();
            $this->assertSame(2, $resource->fresh()->revision);
        }
    }

    public function test_resource_authoring_and_answer_keys_require_explicit_permission(): void
    {
        $this->root();
        $version = $this->createVersion('assessments');
        $editor = User::factory()->create(['is_admin' => true]);
        $this->actingAs($editor)->get('/admin/learning/assessments')->assertForbidden();
        $this->get('/admin/learning/versions/'.$version->id)->assertForbidden();
        $this->postJson('/admin/learning/assessments', $this->payload('assessments'))->assertForbidden();
        $this->get('/admin/programs/create')->assertOk()->assertInertia(fn (Assert $page) => $page->has('learningCatalog', 1)->where('learningCatalog.0.title', $version->title)->missing('learningCatalog.0.content')->missing('learningCatalog.0.questions'));
        $editor->permissions = ['learning.manage'];
        $editor->save();
        $this->get('/admin/learning/versions/'.$version->id)->assertOk();
    }

    public function test_versions_are_immutable_and_stale_updates_do_not_overwrite(): void
    {
        $this->root();
        $old = $this->createVersion('frameworks');
        $payload = $this->payload('frameworks', ['revision' => 1, 'title' => 'Judul baru']);
        $this->post('/admin/learning/frameworks/'.$old->resource_id, $payload)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('Data pengujian', $old->fresh()->title);
        $this->assertSame(2, LearningResource::find($old->resource_id)->revision);
        $this->post('/admin/learning/frameworks/'.$old->resource_id, $payload)->assertSessionHasErrors('revision');
        $this->assertDatabaseCount('learning_resource_versions', 2);
        $this->get('/admin/learning/materials/'.$old->resource_id.'/edit')->assertNotFound();
    }

    public function test_private_pdf_version_is_preserved_and_download_is_authorized(): void
    {
        Storage::fake('learning');
        $this->root();
        $old = $this->createVersion('materials', ['source' => 'pdf', 'url' => null, 'file' => UploadedFile::fake()->create('modul.pdf', 10, 'application/pdf')]);
        Storage::disk('learning')->assertExists($old->file_path);
        $this->get('/admin/learning/versions/'.$old->id.'/file')->assertOk()->assertDownload('modul.pdf');
        $this->post('/admin/learning/materials/'.$old->resource_id, $this->payload('materials', ['revision' => 1, 'source' => 'pdf', 'url' => null, 'file' => UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf')]))->assertRedirect()->assertSessionHasNoErrors();
        $latest = LearningResourceVersion::where('resource_id', $old->resource_id)->where('revision', 2)->firstOrFail();
        $this->assertNotSame($old->file_path, $latest->file_path);
        Storage::disk('learning')->assertExists($old->file_path);
        $this->actingAs(User::factory()->create(['is_admin' => true, 'permissions' => []]))->get('/admin/learning/versions/'.$old->id.'/file')->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/admin/learning/versions/'.$old->id.'/file')->assertOk();
        $this->assertFalse(Storage::disk('public')->exists($old->file_path));
    }

    public function test_bad_file_unsafe_link_and_incomplete_ready_questions_are_rejected_but_draft_is_preserved(): void
    {
        Storage::fake('learning');
        $this->root();
        $this->post('/admin/learning/materials', $this->payload('materials', ['source' => 'pdf', 'file' => UploadedFile::fake()->create('fake.pdf', 1, 'text/plain')]))->assertSessionHasErrors('file');
        $this->post('/admin/learning/materials', $this->payload('materials', ['url' => 'javascript:alert(1)']))->assertSessionHasErrors('url');
        $payload = $this->payload('assessments');
        $payload['questions'][0]['answer'] = 5;
        $this->post('/admin/learning/assessments', $payload)->assertSessionHasErrors('questions.0.answer');
        $payload['status'] = 'draft';
        $this->post('/admin/learning/assessments', $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('learning_resource_versions', 1);
        $this->get('/admin/programs/create')->assertInertia(fn (Assert $page) => $page->has('learningCatalog', 0));
    }

    public function test_pdf_is_cleaned_up_if_database_transaction_fails(): void
    {
        Storage::fake('learning');
        $this->root();
        DB::statement("ALTER TABLE learning_resource_versions ADD CONSTRAINT test_reject_resource CHECK (status <> 'ready')");
        $this->post('/admin/learning/materials', $this->payload('materials', ['source' => 'pdf', 'file' => UploadedFile::fake()->create('modul.pdf', 10, 'application/pdf')]))->assertServerError();
        $this->assertDatabaseCount('learning_resources', 0);
        $this->assertSame([], Storage::disk('learning')->allFiles());
    }

    public function test_framework_mapping_and_material_reuse_survive_resource_edits_and_program_snapshots(): void
    {
        $this->root();
        $framework = $this->createVersion('frameworks');
        $material = $this->createVersion('materials');
        $payload = $this->program(['frameworks' => [['version_id' => $framework->id, 'levels' => ['Tingkat Dasar']]], 'material_versions' => [$material->id]]);
        $payload['content']['subprograms'][0]['levels'][0]['packages'][0]['material_versions'] = [$material->id];
        $id = $this->postJson('/admin/programs', $payload)->assertOk()->json('id');
        $this->post('/admin/learning/frameworks/'.$framework->resource_id, $this->payload('frameworks', ['revision' => 1, 'levels' => ['Tingkatan baru']]))->assertSessionHasNoErrors();
        $program = AcademicProgram::findOrFail($id);
        $this->assertSame($framework->id, $program->published['subprograms'][0]['levels'][0]['frameworks'][0]['version_id']);
        $this->assertSame(['Tingkat Dasar', 'Tingkat Lanjut'], $framework->fresh()->content['levels']);
        $payload['content']['subprograms'][0]['levels'][0]['frameworks'][0]['levels'] = ['Tidak ada'];
        $payload['client_id'] = (string) Str::uuid();
        $this->postJson('/admin/programs', $payload)->assertUnprocessable()->assertJsonValidationErrors('content.subprograms.0.levels.0.frameworks.0.levels');
    }

    public function test_assessment_scope_and_resource_kind_are_checked_on_server(): void
    {
        $this->root();
        $assessment = $this->createVersion('assessments');
        $payload = ['revision' => 1, 'action' => 'publish', 'content' => $this->examProgram->draft];
        $payload['content']['subprograms'][0]['levels'][0]['assessment_versions'] = [$assessment->id];
        $this->putJson('/admin/programs/'.$this->examProgram->id, $payload)->assertUnprocessable()->assertJsonValidationErrors('content.subprograms.0.levels.0.assessment_versions.0');
        unset($payload['content']['subprograms'][0]['levels'][0]['assessment_versions']);
        $payload['content']['subprograms'][0]['levels'][0]['packages'][0]['assessment_versions'] = [$assessment->id];
        $this->putJson('/admin/programs/'.$this->examProgram->id, $payload)->assertOk();
        $payload = $this->program(['material_versions' => [$assessment->id]]);
        $this->postJson('/admin/programs', $payload)->assertUnprocessable();
    }

    public function test_text_questions_need_a_grading_guide_and_draft_empty_arrays_are_supported(): void
    {
        $this->root();
        $this->createVersion('assessments', ['status' => 'draft', 'questions' => []]);
        $this->createVersion('frameworks', ['levels' => []]);
        $payload = $this->payload('assessments', ['questions' => [['id' => (string) Str::uuid(), 'type' => 'text', 'prompt' => 'Tuliskan jawaban', 'options' => [], 'answer' => null, 'guide' => '']]]);
        $this->post('/admin/learning/assessments', $payload)->assertSessionHasErrors('questions.0.guide');
        $payload['questions'][0]['guide'] = 'Jawaban diperiksa berdasarkan isi.';
        $this->post('/admin/learning/assessments', $payload)->assertSessionHasNoErrors();
    }

    public function test_level_module_and_session_sections_persist_without_changing_previous_publication(): void
    {
        $this->root();
        $payload = $this->program(['module_title' => 'Modul Dasar Tong Yi', 'module_description' => 'Bahan ajar untuk delapan sesi.']);
        $payload['content']['subprograms'][0]['levels'][0]['packages'][0]['sessions'][0]['module_section'] = 'Bab 1, halaman 3–8';
        $id = $this->postJson('/admin/programs', $payload)->assertOk()->json('id');
        $program = AcademicProgram::findOrFail($id);
        $this->assertSame('Modul Dasar Tong Yi', $program->published['subprograms'][0]['levels'][0]['module_title']);
        $this->assertSame('Bab 1, halaman 3–8', $program->published['subprograms'][0]['levels'][0]['packages'][0]['sessions'][0]['module_section']);
        $payload['revision'] = 1;
        $payload['action'] = 'draft';
        $payload['content']['subprograms'][0]['levels'][0]['module_title'] = 'Modul diperbarui';
        $this->putJson('/admin/programs/'.$id, $payload)->assertOk();
        $program->refresh();
        $this->assertSame('Modul Dasar Tong Yi', $program->published['subprograms'][0]['levels'][0]['module_title']);
        $this->assertSame('Modul diperbarui', $program->draft['subprograms'][0]['levels'][0]['module_title']);
        $payload['revision'] = 2;
        $payload['content']['subprograms'][0]['levels'][0]['packages'][0]['sessions'][0]['module_section'] = str_repeat('a', 256);
        $this->putJson('/admin/programs/'.$id, $payload)->assertUnprocessable()->assertJsonValidationErrors('content.subprograms.0.levels.0.packages.0.sessions.0.module_section');
        $this->assertSame(2, $program->fresh()->revision);
    }

    private function uploadSession(): LearningResourceVersion
    {
        $response = $this->post('/admin/learning/session-materials', ['title' => 'Materi sesi uji', 'file' => UploadedFile::fake()->create('sesi.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])->assertOk();

        return LearningResourceVersion::findOrFail($response->json('id'));
    }

    public function test_session_pdf_upload_is_private_and_separate_from_teacher_library(): void
    {
        Storage::fake('learning');
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $version = $this->uploadSession();
        $this->assertSame('session', $version->content['usage']);
        $this->assertSame($admin->id, $version->actor_id);
        Storage::disk('learning')->assertExists($version->file_path);
        $this->get('/admin/learning/versions/'.$version->id.'/file')->assertOk();
        $this->get('/admin/programs/create')->assertInertia(fn (Assert $page) => $page->has('learningCatalog', 1)->where('learningCatalog.0.usage', 'session')->missing('learningCatalog.0.file_path'));
        $this->actingAs(User::factory()->create(['is_admin' => true, 'permissions' => []]))
            ->postJson('/admin/learning/session-materials', [])->assertForbidden();
        $this->root();
        $this->get('/admin/learning/materials')->assertInertia(fn (Assert $page) => $page->has('resources.data', 0));
    }

    public function test_session_material_cannot_be_newly_reused_or_replaced_by_teacher_reference(): void
    {
        Storage::fake('learning');
        $this->root();
        $sessionVersion = $this->uploadSession();
        $reference = $this->createVersion('materials');
        $payload = $this->program(['material_versions' => [$reference->id]]);
        $sessions = &$payload['content']['subprograms'][0]['levels'][0]['packages'][0]['sessions'];
        $sessions[0]['material_versions'] = [$sessionVersion->id];
        $id = $this->postJson('/admin/programs', $payload)->assertOk()->json('id');
        $payload['revision'] = 1;
        $sessions[1]['material_versions'] = [$sessionVersion->id];
        $this->putJson('/admin/programs/'.$id, $payload)->assertUnprocessable()->assertJsonValidationErrors('content.subprograms.0.levels.0.packages.0.sessions.1.material_versions.0');
        $sessions[1]['material_versions'] = [$reference->id];
        $this->putJson('/admin/programs/'.$id, $payload)->assertUnprocessable()->assertJsonValidationErrors('content.subprograms.0.levels.0.packages.0.sessions.1.material_versions.0');
        unset($sessions[1]['material_versions']);
        $payload['content']['subprograms'][0]['levels'][0]['material_versions'] = [$sessionVersion->id];
        $this->putJson('/admin/programs/'.$id, $payload)->assertUnprocessable()->assertJsonValidationErrors('content.subprograms.0.levels.0.material_versions.0');
        $this->assertSame(1, AcademicProgram::findOrFail($id)->revision);
    }

    public function test_session_upload_rejects_non_pdf_and_cleans_up_if_transaction_fails(): void
    {
        Storage::fake('learning');
        $this->root();
        $this->post('/admin/learning/session-materials', ['file' => UploadedFile::fake()->create('fake.pdf', 1, 'text/plain')], ['Accept' => 'application/json'])->assertUnprocessable();
        DB::statement("ALTER TABLE learning_resource_versions ADD CONSTRAINT test_reject_session CHECK (status <> 'ready')");
        $this->post('/admin/learning/session-materials', ['file' => UploadedFile::fake()->create('sesi.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])->assertServerError();
        $this->assertSame([], Storage::disk('learning')->allFiles());
        $this->assertDatabaseCount('learning_resources', 0);
    }

    public function test_legacy_shared_session_links_are_preserved_when_unchanged(): void
    {
        $root = $this->root();
        $version = $this->createVersion('materials');
        $legacy = $version->content;
        unset($legacy['usage']);
        $version->content = $legacy;
        $version->save();
        $payload = $this->program();
        $payload['content']['subprograms'][0]['levels'][0]['packages'][0]['sessions'][0]['material_versions'] = [$version->id];
        $payload['content']['subprograms'][0]['levels'][0]['packages'][0]['sessions'][1]['material_versions'] = [$version->id];
        $program = new AcademicProgram;
        $program->draft = $payload['content'];
        $program->published = $payload['content'];
        $program->revision = 1;
        $program->created_by = $root->id;
        $program->save();
        $payload['revision'] = 1;
        $payload['content']['description'] = 'Pembaruan tanpa membuang tautan lama.';
        $this->putJson('/admin/programs/'.$program->id, $payload)->assertOk();
        $this->assertSame([$version->id], $program->fresh()->draft['subprograms'][0]['levels'][0]['packages'][0]['sessions'][1]['material_versions']);
    }

    public function test_pdf_preview_is_inline_private_and_checks_authorization_and_missing_file(): void
    {
        Storage::fake('learning');
        $this->root();
        $version = $this->uploadSession();
        $url = '/admin/learning/versions/'.$version->id.'/preview';
        $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $this->assertStringStartsWith('inline;', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->actingAs(User::factory()->create(['is_admin' => true, 'permissions' => []]))->get($url)->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get($url)->assertOk();
        Storage::disk('learning')->delete($version->file_path);
        $this->get($url)->assertNotFound();
    }

    public function test_each_session_accepts_only_one_pdf_in_new_assignments(): void
    {
        Storage::fake('learning');
        $this->root();
        $first = $this->uploadSession();
        $second = $this->uploadSession();
        $payload = $this->program();
        $payload['content']['subprograms'][0]['levels'][0]['packages'][0]['sessions'][0]['material_versions'] = [$first->id, $second->id];
        $this->postJson('/admin/programs', $payload)->assertUnprocessable()->assertJsonValidationErrors('content.subprograms.0.levels.0.packages.0.sessions.0.material_versions');
        $payload['content']['subprograms'][0]['levels'][0]['packages'][0]['sessions'][0]['material_versions'] = [$first->id];
        $id = $this->postJson('/admin/programs', $payload)->assertOk()->json('id');
        $payload['revision'] = 1;
        $payload['content']['subprograms'][0]['levels'][0]['packages'][0]['sessions'][0]['material_versions'] = [$second->id];
        $this->putJson('/admin/programs/'.$id, $payload)->assertOk();
        Storage::disk('learning')->assertExists($first->file_path);
    }
}
