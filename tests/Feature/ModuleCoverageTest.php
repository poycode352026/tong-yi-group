<?php

namespace Tests\Feature;

use App\Models\LearningResourceVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ModuleCoverageTest extends TestCase
{
    use RefreshDatabase;

    private string $url = '/admin/learning/materials/coverage';

    private function admin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true, 'is_superadmin' => true]));
    }

    private function coverage(): array
    {
        return $this->postJson($this->url, ['revision' => 0, 'title' => 'HSK', 'status' => 'ready', 'description' => '', 'levels' => ['HSK 1', 'HSK 2']])->assertOk()->json();
    }

    public function test_coverage_can_be_created_edited_without_duplicate_versions_and_revision_checked(): void
    {
        $this->admin();
        $row = $this->coverage();
        $this->get($this->url)->assertInertia(fn (AssertableInertia $page) => $page->component('Admin/Learning/Coverage')->has('coverage', 1));
        $payload = ['revision' => 1, 'title' => 'HSK baru', 'status' => 'ready', 'description' => 'Keterangan', 'levels' => ['Dasar', 'HSK 3']];
        $this->postJson($this->url.'/'.$row['id'], $payload)->assertOk()->assertJsonPath('revision', 2)->assertJsonPath('levels', ['Dasar', 'HSK 3']);
        $this->postJson($this->url.'/'.$row['id'], $payload)->assertUnprocessable()->assertJsonValidationErrors('revision');
        $payload['revision'] = 2;
        $this->postJson($this->url.'/'.$row['id'], $payload)->assertOk()->assertJsonPath('revision', 2);
        $this->assertSame(['HSK 1', 'HSK 2'], LearningResourceVersion::where('resource_id', $row['id'])->where('revision', 1)->first()->content['levels']);
        $this->assertSame(2, LearningResourceVersion::where('resource_id', $row['id'])->count());
    }

    public function test_archive_preserves_module_references_blocks_new_references_and_can_be_restored(): void
    {
        $this->admin();
        $row = $this->coverage();
        $version = LearningResourceVersion::where('resource_id', $row['id'])->firstOrFail();
        $module = ['revision' => 0, 'format' => 'module_catalog', 'title' => 'Modul dasar', 'status' => 'ready', 'references' => ['internal' => false, 'frameworks' => [['version_id' => $version->id, 'levels' => ['HSK 1']]]], 'attachments' => [['id' => (string) Str::uuid(), 'title' => 'Bahan ajar', 'source' => 'link', 'url' => 'https://example.org/material']]];
        $saved = $this->postJson('/admin/learning/materials', $module)->assertOk()->json();
        $this->postJson($this->url.'/'.$row['id'].'/archive', ['revision' => 1])->assertOk()->assertJsonPath('archived', true)->assertJsonPath('revision', 2);
        $this->postJson($this->url.'/'.$row['id'].'/archive', ['revision' => 2])->assertOk()->assertJsonPath('revision', 2);
        $this->postJson($this->url.'/'.$row['id'].'/restore', ['revision' => 1])->assertConflict();
        $this->postJson('/admin/learning/materials', $module)->assertUnprocessable()->assertJsonValidationErrors('references.frameworks.0.version_id');
        $module['revision'] = 1;
        $module['description'] = 'Ubah keterangan, cakupan lama tetap';
        $this->postJson('/admin/learning/materials/'.$saved['resource_id'], $module)->assertOk();
        $module['revision'] = 2;
        $module['references']['frameworks'][0]['levels'] = ['HSK 2'];
        $this->postJson('/admin/learning/materials/'.$saved['resource_id'], $module)->assertUnprocessable()->assertJsonValidationErrors('references.frameworks.0.version_id');
        $this->assertNotNull($version->fresh());
        $this->postJson($this->url.'/'.$row['id'].'/restore', ['revision' => 2])->assertOk()->assertJsonPath('archived', false)->assertJsonPath('revision', 3);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'coverage.archived', 'subject_id' => $row['id']]);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'coverage.restored', 'subject_id' => $row['id']]);
    }

    public function test_coverage_requires_learning_permission(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true, 'is_superadmin' => false]));
        $this->get($this->url)->assertForbidden();
        $this->postJson($this->url, [])->assertForbidden();
    }
}
