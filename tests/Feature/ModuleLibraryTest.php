<?php

namespace Tests\Feature;

use App\Models\AcademicProgram;
use App\Models\LearningResource;
use App\Models\LearningResourceVersion;
use App\Models\User;
use App\Support\AdminNavigation;
use App\Support\ModuleFiles;
use Database\Seeders\LearningFrameworkSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ModuleLibraryTest extends TestCase
{
    use RefreshDatabase;

    private function setupLibrary(): string
    {
        Storage::fake('learning');
        $this->actingAs(User::factory()->create(['is_admin' => true, 'is_superadmin' => true]));
        $this->seed(LearningFrameworkSeeder::class);

        return LearningResourceVersion::where('title', 'HSK')->value('id');
    }

    private function row(string $title): array
    {
        return ['id' => (string) Str::uuid(), 'title' => $title, 'purpose' => 'Panduan mengajar', 'description' => 'Keterangan khusus '.$title, 'source' => 'pdf', 'file' => UploadedFile::fake()->create($title.'.pdf', 10, 'application/pdf')];
    }

    private function payload(string $framework): array
    {
        return ['format' => 'module', 'title' => 'Judul dari klien', 'revision' => 0, 'status' => 'ready', 'framework_version_id' => $framework, 'framework_level' => 'HSK 1', 'description' => 'Modul guru', 'attachments' => [$this->row('Buku utama'), $this->row('Panduan guru')]];
    }

    private function create(array $payload): LearningResourceVersion
    {
        $ids = LearningResource::pluck('id');
        $this->post('/admin/learning/materials', $payload)->assertRedirect()->assertSessionHasNoErrors();
        $resource = LearningResource::whereNotIn('id', $ids)->firstOrFail();

        return $resource->versions()->where('revision', 1)->firstOrFail();
    }

    private function levelProgram(): AcademicProgram
    {
        $program = new AcademicProgram;
        $program->created_by = auth()->id();
        $program->revision = 1;
        $program->draft = ['name' => 'Mandarin', 'category' => 'Bahasa', 'description' => '', 'subprograms' => [['id' => (string) Str::uuid(), 'name' => 'Kerja', 'audience' => '', 'variant' => '', 'levels' => [['id' => (string) Str::uuid(), 'name' => 'Absolute Beginner–Beginner', 'package_count' => 1, 'services' => [], 'packages' => [['id' => (string) Str::uuid(), 'theme' => '', 'sessions' => array_map(fn () => ['id' => (string) Str::uuid(), 'title' => '', 'objectives' => '', 'material' => '', 'exercise' => '', 'reference' => ''], range(1, 8))]]]]]]];
        $program->save();

        return $program;
    }

    private function levelPayload(AcademicProgram $program): array
    {
        $sub = $program->draft['subprograms'][0];

        return ['references' => ['internal' => true, 'frameworks' => []], 'format' => 'module_level', 'revision' => 0, 'title' => 'Modul Tong Yi', 'status' => 'ready',
            'level_target' => ['program_id' => $program->id, 'program_revision' => $program->revision, 'subprogram_id' => $sub['id'], 'level_id' => $sub['levels'][0]['id']],
            'attachments' => [$this->row('Buku HSK 1'), $this->row('Bahan Tong Yi')]];
    }

    public function test_catalog_attachment_coverage_must_use_selected_module_levels(): void
    {
        $hsk = $this->setupLibrary();
        $payload = ['format' => 'module_catalog', 'revision' => 0, 'title' => 'Modul dasar', 'status' => 'ready', 'references' => ['internal' => false, 'frameworks' => [['version_id' => $hsk, 'levels' => ['HSK 1', 'HSK 2']]]], 'attachments' => [$this->row('Buku pertama'), $this->row('Buku kedua')]];
        $payload['attachments'][0]['references'] = ['internal' => false, 'frameworks' => [['version_id' => $hsk, 'levels' => ['HSK 1']]]];
        $payload['attachments'][1]['references'] = ['internal' => false, 'frameworks' => [['version_id' => $hsk, 'levels' => ['HSK 2']]]];
        $saved = $this->postJson('/admin/learning/materials', $payload)->assertOk();
        $this->assertSame(['HSK: HSK 1'], $saved->json('attachments.0.reference_labels'));
        $this->assertSame(['HSK: HSK 2'], $saved->json('attachments.1.reference_labels'));
        $payload['revision'] = 1;
        $payload['attachments'] = $saved->json('attachments');
        $this->postJson('/admin/learning/materials/'.$saved->json('resource_id'), $payload)->assertOk()->assertJsonPath('revision', 1);
        $payload['attachments'][0]['references']['frameworks'][0]['levels'] = ['HSK 3'];
        $this->postJson('/admin/learning/materials/'.$saved->json('resource_id'), $payload)->assertUnprocessable()->assertJsonValidationErrors('attachments.0.references');
    }

    public function test_named_module_saves_multiple_coverage_standards_without_group_or_level_binding(): void
    {
        $hsk = $this->setupLibrary();
        $bct = LearningResourceVersion::whereHas('resource', fn ($q) => $q->where('kind', 'frameworks'))->get()->first(fn ($v) => in_array('BCT A', $v->content['levels'] ?? []));
        $payload = ['format' => 'module_catalog', 'revision' => 0, 'title' => 'Absolute Beginner - Beginner', 'status' => 'ready', 'references' => ['internal' => false, 'frameworks' => [['version_id' => $hsk, 'levels' => ['HSK 1', 'HSK 2']], ['version_id' => $bct->id, 'levels' => ['BCT A']]]], 'attachments' => [$this->row('Materi Tong Yi')]];
        $response = $this->postJson('/admin/learning/materials', $payload)->assertOk()->assertJsonPath('title', 'Absolute Beginner - Beginner');
        $version = LearningResourceVersion::findOrFail($response->json('id'));
        $this->assertArrayNotHasKey('group', $version->content);
        $this->assertArrayNotHasKey('level_target', $version->content);
        $this->assertContains('HSK: HSK 1, HSK 2', $response->json('reference_labels'));
        $this->assertCount(2, $response->json('reference_labels'));
        $this->assertCount(2, $version->content['references']['frameworks']);
        $payload['revision'] = 1;
        $payload['references']['frameworks'] = array_reverse($payload['references']['frameworks']);
        $payload['references']['frameworks'][1]['levels'] = ['HSK 2', 'HSK 1'];
        $payload['attachments'] = $response->json('attachments');
        $payload['references'] ??= ['internal' => false, 'frameworks' => []];
        $this->post('/admin/learning/materials/'.$version->resource_id, $payload)->assertSessionHasNoErrors()->assertSessionHas('success', 'Tidak ada perubahan. Versi tetap sama.');
    }

    public function test_selected_standard_requires_matching_coverage_level(): void
    {
        $hsk = $this->setupLibrary();
        $payload = ['format' => 'module_catalog', 'revision' => 0, 'title' => 'Absolute Beginner', 'status' => 'ready', 'references' => ['internal' => false, 'frameworks' => [['version_id' => $hsk, 'levels' => []]]], 'attachments' => [$this->row('Materi')]];
        $this->post('/admin/learning/materials', $payload)->assertSessionHasErrors('references.frameworks.0.levels');
        $payload['references']['frameworks'][0]['levels'] = ['BCT A'];
        $this->post('/admin/learning/materials', $payload)->assertSessionHasErrors('references.frameworks.0.levels');
        $this->assertDatabaseCount('learning_resource_files', 0);
        $this->assertSame([], Storage::disk('learning')->allFiles());
    }

    public function test_general_catalog_has_group_and_materials_without_level_or_reference_form(): void
    {
        $this->setupLibrary();
        $payload = ['format' => 'module_catalog', 'group' => 'HSK', 'title' => 'HSK 1', 'revision' => 0, 'status' => 'ready', 'attachments' => [$this->row('Buku utama'), ['id' => (string) Str::uuid(), 'title' => 'Latihan kosakata', 'purpose' => 'Referensi', 'description' => 'Tautan pengajaran', 'source' => 'link', 'url' => 'https://example.org/hsk1']]];
        $response = $this->postJson('/admin/learning/materials', $payload)->assertOk()->assertJsonPath('group', 'HSK')->assertJsonPath('title', 'HSK 1');
        $version = LearningResourceVersion::findOrFail($response->json('id'));
        $this->assertArrayNotHasKey('level_target', $version->content);
        $this->assertArrayNotHasKey('references', $version->content);
        $this->assertCount(2, $response->json('attachments'));
        $payload['revision'] = 1;
        $payload['attachments'] = $response->json('attachments');
        $payload['references'] ??= ['internal' => false, 'frameworks' => []];
        $this->post('/admin/learning/materials/'.$version->resource_id, $payload)->assertSessionHasNoErrors()->assertSessionHas('success', 'Tidak ada perubahan. Versi tetap sama.');
        $this->assertSame(1, $version->resource->fresh()->revision);
        foreach ([$this->levelProgram(), $this->levelProgram()] as $program) {
            $content = $program->draft;
            $content['subprograms'][0]['levels'][0]['material_versions'] = [$version->id];
            $this->putJson('/admin/programs/'.$program->id, ['revision' => 1, 'action' => 'publish', 'content' => $content])->assertOk();
        }
        $this->get('/admin/learning/materials?group=HSK')->assertOk()->assertInertia(fn (AssertableInertia $page) => $page->has('resources.data', 1)->where('resources.data.0.group', 'HSK'));
        $this->get('/admin/learning/materials?group=BCT')->assertOk()->assertInertia(fn (AssertableInertia $page) => $page->has('resources.data', 0));
        $nav = AdminNavigation::forUser(auth()->user());
        $this->assertTrue(collect($nav)->firstWhere('key', 'frameworks')['hidden']);
        $this->assertSame('Modul', collect($nav)->firstWhere('key', 'materials')['label']);
    }

    public function test_catalog_validates_coverage_and_preserves_old_module_metadata_and_files_when_updated(): void
    {
        $framework = $this->setupLibrary();
        $old = $this->create($this->payload($framework));
        $payload = ['format' => 'module_catalog', 'group' => '', 'title' => 'HSK 1', 'revision' => 1, 'status' => 'ready', 'attachments' => ModuleFiles::present($old)];
        $payload['references'] = ['internal' => false, 'frameworks' => [['version_id' => $framework, 'levels' => ['Bukan tingkatan HSK']]]];
        $this->post('/admin/learning/materials/'.$old->resource_id, $payload)->assertSessionHasErrors('references.frameworks.0.levels');
        unset($payload['references']);
        $payload['group'] = 'HSK';
        $this->post('/admin/learning/materials/'.$old->resource_id, $payload)->assertSessionHasNoErrors();
        $current = $old->resource->versions()->where('revision', 2)->firstOrFail();
        $this->assertSame('HSK', $current->content['group']);
        $this->assertSame('HSK 1', $current->content['legacy_metadata']['framework_level']);
        $this->assertSame($old->content['attachments'], $current->content['attachments']);
        $this->assertSame('module', $old->fresh()->content['format']);
        $this->assertDatabaseCount('learning_resource_files', 2);
        $this->get(ModuleFiles::present($old)[0]['preview_url'])->assertOk();
    }

    public function test_module_references_inherit_per_file_and_allow_independent_internal_or_specific_tags(): void
    {
        $framework = $this->setupLibrary();
        $payload = $this->levelPayload($this->levelProgram());
        $payload['references'] = ['internal' => false, 'frameworks' => [['version_id' => $framework, 'levels' => ['HSK 1', 'HSK 2']]]];
        $payload['attachments'][1]['references'] = ['internal' => true, 'frameworks' => []];
        $payload['attachments'][] = [...$this->row('Buku khusus HSK 1'), 'references' => ['internal' => false, 'frameworks' => [['version_id' => $framework, 'levels' => ['HSK 1']]]]];
        $old = $this->create($payload);
        $files = ModuleFiles::present($old);
        $this->assertSame(['HSK: HSK 1, HSK 2'], $files[0]['reference_labels']);
        $this->assertSame(['Internal Tong Yi'], $files[1]['reference_labels']);
        $this->assertSame(['HSK: HSK 1'], $files[2]['reference_labels']);
        $payload['revision'] = 1;
        $payload['attachments'] = $files;
        $payload['attachments'][1]['references']['internal'] = '1';
        $payload['references']['frameworks'][0]['levels'] = ['HSK 2', 'HSK 1'];
        $this->post('/admin/learning/materials/'.$old->resource_id, $payload)->assertSessionHasNoErrors()->assertSessionHas('success', 'Tidak ada perubahan. Versi tetap sama.');
        $payload['references']['frameworks'][0]['levels'] = ['HSK 2'];
        $this->post('/admin/learning/materials/'.$old->resource_id, $payload)->assertSessionHasNoErrors();
        $new = $old->resource->versions()->where('revision', 2)->firstOrFail();
        $this->assertSame(['HSK: HSK 2'], ModuleFiles::present($new)[0]['reference_labels']);
        $this->assertSame(['Internal Tong Yi'], ModuleFiles::present($new)[1]['reference_labels']);
        $this->assertSame(['HSK: HSK 1, HSK 2'], ModuleFiles::present($old->fresh())[0]['reference_labels']);
        $this->assertDatabaseCount('learning_resource_files', 3);
    }

    public function test_reference_tags_require_real_framework_levels_or_internal_tag(): void
    {
        $framework = $this->setupLibrary();
        $payload = $this->levelPayload($this->levelProgram());
        $payload['references'] = ['internal' => false, 'frameworks' => []];
        $this->post('/admin/learning/materials', $payload)->assertSessionHasErrors('references');
        $payload['references'] = ['internal' => true, 'frameworks' => []];
        $payload['attachments'][0]['references'] = ['internal' => false, 'frameworks' => [['version_id' => $framework, 'levels' => ['BCT B']]]];
        $this->post('/admin/learning/materials', $payload)->assertSessionHasErrors('attachments.0.references.frameworks.0.levels');
        $payload['attachments'][0]['references']['frameworks'][0]['version_id'] = (string) Str::uuid();
        $this->post('/admin/learning/materials', $payload)->assertSessionHasErrors('attachments.0.references.frameworks.0.version_id');
        $this->assertDatabaseCount('learning_resource_files', 0);
    }

    public function test_module_accepts_multiple_files_and_can_be_reused_across_levels(): void
    {
        $this->setupLibrary();
        $program = $this->levelProgram();
        $payload = $this->levelPayload($program);
        $response = $this->postJson('/admin/learning/materials', $payload)->assertOk()->assertJsonPath('title', 'Modul Tong Yi')->assertJsonPath('level_target.level_name', 'Absolute Beginner–Beginner');
        $id = $response->json('id');
        $resource = LearningResourceVersion::findOrFail($id)->resource;
        $this->assertCount(2, $response->json('attachments'));
        $this->assertArrayNotHasKey('framework_version_id', LearningResourceVersion::findOrFail($id)->content);
        $this->assertArrayNotHasKey('file_path', $response->json());
        $payload['revision'] = 1;
        $payload['attachments'] = $response->json('attachments');
        $this->post('/admin/learning/materials/'.$resource->id, $payload)->assertSessionHasNoErrors()->assertSessionHas('success', 'Tidak ada perubahan. Versi tetap sama.');
        $content = $program->draft;
        $content['subprograms'][0]['levels'][0]['material_versions'] = [$id];
        $this->putJson('/admin/programs/'.$program->id, ['revision' => 1, 'action' => 'publish', 'content' => $content])->assertOk();
        $other = $this->levelProgram();
        $content = $other->draft;
        $content['subprograms'][0]['levels'][0]['material_versions'] = [$id];
        $this->putJson('/admin/programs/'.$other->id, ['revision' => 1, 'action' => 'publish', 'content' => $content])->assertOk();
        $content = $program->fresh()->draft;
        $content['subprograms'][0]['levels'][0]['packages'][0]['sessions'][0]['material_versions'] = [$id];
        $this->putJson('/admin/programs/'.$program->id, ['revision' => 2, 'action' => 'publish', 'content' => $content])->assertUnprocessable();
    }

    public function test_level_module_rejects_wrong_parent_and_stale_program_without_leaking_files(): void
    {
        $this->setupLibrary();
        $program = $this->levelProgram();
        $payload = $this->levelPayload($program);
        $payload['level_target']['level_id'] = (string) Str::uuid();
        $this->post('/admin/learning/materials', $payload)->assertSessionHasErrors('level_target.level_id');
        $payload = $this->levelPayload($program);
        $program->revision = 2;
        $program->save();
        $this->post('/admin/learning/materials', $payload)->assertSessionHasErrors('level_target.program_id');
        $this->assertDatabaseCount('learning_resource_files', 0);
        $this->assertSame([], Storage::disk('learning')->allFiles());
    }

    public function test_old_hsk_module_can_be_grouped_into_tong_yi_level_without_losing_old_pdf(): void
    {
        $framework = $this->setupLibrary();
        $old = $this->create($this->payload($framework));
        $program = $this->levelProgram();
        $payload = $this->levelPayload($program);
        $payload['revision'] = 1;
        $payload['attachments'] = ModuleFiles::present($old);
        $this->post('/admin/learning/materials/'.$old->resource_id, $payload)->assertSessionHasNoErrors();
        $latest = $old->resource->versions()->where('revision', 2)->firstOrFail();
        $this->assertSame($program->id, $latest->content['level_target']['program_id']);
        $this->assertSame('HSK 1', $old->fresh()->content['framework_level']);
        $this->assertDatabaseCount('learning_resource_files', 2);
        $this->get(ModuleFiles::present($old)[0]['preview_url'])->assertOk();
    }

    public function test_unchanged_module_and_identical_reupload_keep_same_version(): void
    {
        $framework = $this->setupLibrary();
        $payload = $this->payload($framework);
        $old = $this->create($payload);
        $payload['revision'] = 1;
        $payload['attachments'] = ModuleFiles::present($old);
        $logs = DB::table('admin_audit_logs')->count();
        $this->post('/admin/learning/materials/'.$old->resource_id, $payload)->assertSessionHasNoErrors()->assertSessionHas('success', 'Tidak ada perubahan. Versi tetap sama.');
        $payload['attachments'][0]['file'] = UploadedFile::fake()->create('Buku utama.pdf', 10, 'application/pdf');
        $this->post('/admin/learning/materials/'.$old->resource_id, $payload)->assertSessionHasNoErrors()->assertSessionHas('success', 'Tidak ada perubahan. Versi tetap sama.');
        $this->assertSame(1, $old->resource->fresh()->revision);
        $this->assertDatabaseCount('learning_resource_files', 2);
        $this->assertCount(2, Storage::disk('learning')->allFiles());
        $this->assertSame($logs, DB::table('admin_audit_logs')->count());
    }

    public function test_module_uses_existing_framework_level_and_has_multiple_private_files(): void
    {
        $framework = $this->setupLibrary();
        $version = $this->create($this->payload($framework));
        $this->assertSame('Modul HSK 1', $version->title);
        $this->assertSame($framework, $version->content['framework_version_id']);
        $this->assertCount(2, $version->content['attachments']);
        $this->assertDatabaseCount('learning_resource_files', 2);
        $rows = ModuleFiles::present($version);
        $this->assertSame('Panduan mengajar', $rows[1]['purpose']);
        $this->assertSame('Keterangan khusus Panduan guru', $rows[1]['description']);
        $this->get($rows[0]['file_url'])->assertOk()->assertDownload('Buku utama.pdf');
        $response = $this->get($rows[1]['preview_url'])->assertOk();
        $this->assertStringStartsWith('inline;', $response->headers->get('Content-Disposition'));
        $this->actingAs(User::factory()->create(['is_admin' => true, 'permissions' => []]))->get($rows[0]['file_url'])->assertForbidden();
    }

    public function test_invalid_framework_level_is_rejected_before_files_are_stored(): void
    {
        $framework = $this->setupLibrary();
        $payload = $this->payload($framework);
        $payload['framework_level'] = 'BCT B';
        $this->post('/admin/learning/materials', $payload)->assertSessionHasErrors('framework_level');
        $this->assertDatabaseCount('learning_resource_files', 0);
        $this->assertSame([], Storage::disk('learning')->allFiles());
    }

    public function test_file_metadata_edits_and_removal_do_not_overwrite_old_versions(): void
    {
        $framework = $this->setupLibrary();
        $old = $this->create($this->payload($framework));
        $payload = $this->payload($framework);
        $payload['revision'] = 1;
        $payload['attachments'] = [$old->content['attachments'][0]];
        $payload['attachments'][0]['description'] = 'Deskripsi baru';
        $this->post('/admin/learning/materials/'.$old->resource_id, $payload)->assertSessionHasNoErrors();
        $new = LearningResourceVersion::where('resource_id', $old->resource_id)->where('revision', 2)->firstOrFail();
        $this->assertCount(1, $new->content['attachments']);
        $this->assertCount(2, $old->fresh()->content['attachments']);
        $this->assertNotSame('Deskripsi baru', $old->fresh()->content['attachments'][0]['description']);
        $this->assertDatabaseCount('learning_resource_files', 2);
        $this->get(ModuleFiles::present($old)[1]['file_url'])->assertOk();
    }

    public function test_cross_module_file_reference_is_rejected_and_new_upload_is_rolled_back(): void
    {
        $framework = $this->setupLibrary();
        $old = $this->create($this->payload($framework));
        $payload = $this->payload($framework);
        $payload['attachments'][1] = $old->content['attachments'][1];
        $this->post('/admin/learning/materials', $payload)->assertSessionHasErrors('attachments.1.file');
        $this->assertDatabaseCount('learning_resource_files', 2);
        $this->assertDatabaseCount('learning_resources', 3);
        $this->assertCount(2, Storage::disk('learning')->allFiles());
    }

    public function test_legacy_pdf_can_be_grouped_without_losing_the_original(): void
    {
        $framework = $this->setupLibrary();
        $old = $this->create(['title' => 'Modul lama', 'revision' => 0, 'status' => 'ready', 'source' => 'pdf', 'file' => UploadedFile::fake()->create('lama.pdf', 10, 'application/pdf')]);
        $payload = $this->payload($framework);
        $payload['revision'] = 1;
        $payload['attachments'] = [['id' => (string) Str::uuid(), 'title' => 'Buku lama', 'purpose' => 'Referensi', 'description' => '', 'source' => 'pdf', 'legacy_version_id' => $old->id]];
        $this->post('/admin/learning/materials/'.$old->resource_id, $payload)->assertSessionHasNoErrors();
        $new = LearningResourceVersion::where('resource_id', $old->resource_id)->where('revision', 2)->firstOrFail();
        $this->get('/admin/learning/versions/'.$old->id.'/file')->assertOk();
        $this->get(ModuleFiles::present($new)[0]['file_url'])->assertOk()->assertDownload('lama.pdf');
        Storage::disk('learning')->assertExists($old->file_path);
    }
}
