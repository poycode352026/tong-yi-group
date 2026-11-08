<?php

namespace Tests\Feature;

use App\Models\AcademicProgram;
use App\Models\LearningResourceVersion;
use App\Models\User;
use App\Support\ExamPlacement;
use App\Support\LearningCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExamPlacementTest extends TestCase
{
    use RefreshDatabase;

    private function program(): AcademicProgram
    {
        $root = User::factory()->create(['is_admin' => true, 'is_superadmin' => true]);
        $this->actingAs($root);
        $packages = [];
        for ($i = 0; $i < 3; $i++) {
            $packages[] = ['id' => (string) Str::uuid(), 'theme' => '', 'sessions' => array_map(fn () => ['id' => (string) Str::uuid(), 'title' => '', 'objectives' => '', 'material' => '', 'exercise' => '', 'reference' => ''], range(1, 8))];
        }
        $program = new AcademicProgram;
        $program->created_by = $root->id;
        $program->revision = 1;
        $program->draft = ['name' => 'Mandarin', 'category' => 'Bahasa', 'description' => '', 'subprograms' => [['id' => (string) Str::uuid(), 'name' => 'Kerja', 'audience' => '', 'variant' => '', 'levels' => [['id' => (string) Str::uuid(), 'name' => 'Absolute Beginner–Beginner', 'package_count' => 3, 'services' => [], 'packages' => $packages]]]]];
        $program->save();

        return $program;
    }

    private function payload(AcademicProgram $program): array
    {
        $sub = $program->draft['subprograms'][0];
        $level = $sub['levels'][0];

        return ['revision' => 0, 'title' => 'Ujian Paket 2', 'status' => 'ready', 'scope' => 'package',
            'target' => ['program_id' => $program->id, 'program_revision' => 1, 'subprogram_id' => $sub['id'], 'level_id' => $level['id'], 'package_id' => $level['packages'][1]['id']],
            'questions' => [['id' => (string) Str::uuid(), 'type' => 'choice', 'prompt' => 'Pilih jawaban', 'options' => ['A', 'B'], 'answer' => 0, 'guide' => '']]];
    }

    public function test_program_catalog_resolves_exam_target_without_manual_links_or_answer_exposure(): void
    {
        $program = $this->program();
        $payload = $this->payload($program);
        $this->post('/admin/learning/assessments', $payload)->assertSessionHasNoErrors();
        $old = LearningResourceVersion::firstOrFail();
        $payload['revision'] = 1;
        $payload['title'] = 'Ujian Paket 2 diperbarui';
        $this->post('/admin/learning/assessments/'.$old->resource_id, $payload)->assertSessionHasNoErrors();
        $catalog = LearningCatalog::forPrograms();
        $current = collect($catalog)->firstWhere('is_current', true);
        $this->assertSame('Ujian Paket 2 diperbarui', $current['title']);
        $this->assertSame(1, $current['questions_count']);
        $this->getJson('/admin/learning/catalog')->assertOk()->assertJsonCount(2)->assertHeader('Cache-Control', 'no-store, private');
        $this->assertSame($program->draft['subprograms'][0]['levels'][0]['packages'][1]['id'], $current['target']['package_id']);
        $this->assertFalse(collect($catalog)->firstWhere('id', $old->id)['is_current']);
        foreach ($catalog as $item) {
            $this->assertArrayNotHasKey('questions', $item);
            $this->assertArrayNotHasKey('answer', $item);
        }
        $this->assertEquals($program->draft, $program->fresh()->draft);
        $this->assertSame(1, $program->fresh()->revision);
        $this->get('/admin/programs/'.$program->id.'/edit')->assertOk()->assertInertia(fn (Assert $page) => $page->has('learningCatalog', 2));
    }

    public function test_question_media_is_private_revisioned_and_removed_without_destroying_old_media(): void
    {
        Storage::fake('learning');
        $program = $this->program();
        $payload = $this->payload($program);
        $payload['questions'][0]['image_file'] = UploadedFile::fake()->image('gambar.png');
        $payload['questions'][0]['audio_file'] = UploadedFile::fake()->create('suara.mp3', 10, 'audio/mpeg');
        $this->post('/admin/learning/assessments', $payload)->assertSessionHasNoErrors();
        $version = LearningResourceVersion::firstOrFail();
        $media = $version->content['questions'][0]['media'];
        Storage::disk('learning')->assertExists($media['image']['file_path']);
        $url = '/admin/learning/versions/'.$version->id.'/questions/'.$payload['questions'][0]['id'].'/image';
        $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/png');
        $payload['revision'] = 1;
        unset($payload['questions'][0]['image_file'], $payload['questions'][0]['audio_file']);
        $payload['questions'][0]['media'] = ['image' => ['file_path' => '/untrusted', 'url' => '/fake']];
        $this->post('/admin/learning/assessments/'.$version->resource_id, $payload)->assertSessionHasNoErrors()->assertSessionHas('success', 'Tidak ada perubahan. Versi tetap sama.');
        $payload['questions'][0]['remove_image'] = true;
        $this->post('/admin/learning/assessments/'.$version->resource_id, $payload)->assertSessionHasNoErrors();
        $latest = LearningResourceVersion::where('revision', 2)->firstOrFail();
        $this->assertArrayNotHasKey('image', $latest->content['questions'][0]['media']);
        $this->get($url)->assertOk();
        $this->get('/admin/learning/versions/'.$latest->id.'/questions/'.$payload['questions'][0]['id'].'/image')->assertNotFound();
        $this->actingAs(User::factory()->create(['is_admin' => true, 'is_superadmin' => false]));
        $this->get($url)->assertForbidden();
    }

    public function test_package_target_and_session_range_are_saved_and_shown_without_extra_revision(): void
    {
        $program = $this->program();
        $payload = $this->payload($program);
        $this->get('/admin/learning/assessments/create')->assertOk()->assertInertia(fn (Assert $page) => $page->has('examPrograms', 1)->where('examPrograms.0.subprograms.0.levels.0.packages.1.sessions', 8));
        $this->post('/admin/learning/assessments', $payload)->assertSessionHasNoErrors();
        $version = LearningResourceVersion::firstOrFail();
        $this->assertSame('Absolute Beginner–Beginner', $version->content['target']['level_name']);
        $this->assertSame(9, $version->content['target']['session_start']);
        $this->assertSame(16, $version->content['target']['session_end']);
        $payload['revision'] = 1;
        $this->post('/admin/learning/assessments/'.$version->resource_id, $payload)->assertSessionHasNoErrors()->assertSessionHas('success', 'Tidak ada perubahan. Versi tetap sama.');
        $this->assertDatabaseCount('learning_resource_versions', 1);
        $this->get('/admin/learning/assessments')->assertOk()->assertInertia(fn (Assert $page) => $page->where('resources.data.0.target.package_name', 'Paket 2'));
    }

    public function test_level_exam_covers_all_active_packages_and_excludes_inactive_ones(): void
    {
        $program = $this->program();
        $payload = $this->payload($program);
        $payload['scope'] = 'level';
        $payload['target']['package_id'] = null;
        $this->post('/admin/learning/assessments', $payload)->assertSessionHasNoErrors();
        $target = LearningResourceVersion::firstOrFail()->content['target'];
        $this->assertSame(1, $target['session_start']);
        $this->assertSame(24, $target['session_end']);
        $this->assertNull($target['package_id']);
        $content = $program->draft;
        $content['subprograms'][0]['levels'][0]['package_count'] = 2;
        $program->draft = $content;
        $program->save();
        $this->assertCount(2, ExamPlacement::options()[0]['subprograms'][0]['levels'][0]['packages']);
    }

    public function test_missing_foreign_inactive_or_stale_target_is_rejected(): void
    {
        $program = $this->program();
        $payload = $this->payload($program);
        $without = $payload;
        unset($without['target']);
        $this->post('/admin/learning/assessments', $without)->assertSessionHasErrors('target');
        $payload['target']['level_id'] = (string) Str::uuid();
        $this->post('/admin/learning/assessments', $payload)->assertSessionHasErrors('target.level_id');
        $payload = $this->payload($program);
        $payload['target']['package_id'] = (string) Str::uuid();
        $this->post('/admin/learning/assessments', $payload)->assertSessionHasErrors('target.package_id');
        $payload = $this->payload($program);
        $program->revision = 2;
        $program->save();
        $this->post('/admin/learning/assessments', $payload)->assertSessionHasErrors('target.program_id');
        $this->assertDatabaseCount('learning_resources', 0);
    }

    public function test_targeted_exam_cannot_be_newly_linked_to_another_package(): void
    {
        $program = $this->program();
        $this->post('/admin/learning/assessments', $this->payload($program))->assertSessionHasNoErrors();
        $exam = LearningResourceVersion::firstOrFail();
        $content = $program->draft;
        $content['subprograms'][0]['levels'][0]['packages'][0]['assessment_versions'] = [$exam->id];
        $this->putJson('/admin/programs/'.$program->id, ['revision' => 1, 'action' => 'publish', 'content' => $content])->assertUnprocessable()->assertJsonValidationErrors('content.subprograms.0.levels.0.packages.0.assessment_versions.0');
        unset($content['subprograms'][0]['levels'][0]['packages'][0]['assessment_versions']);
        $content['subprograms'][0]['levels'][0]['packages'][1]['assessment_versions'] = [$exam->id];
        $this->putJson('/admin/programs/'.$program->id, ['revision' => 1, 'action' => 'publish', 'content' => $content])->assertOk();
    }
}
