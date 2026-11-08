<?php

namespace Tests\Feature;

use App\Models\LearningResource;
use App\Models\LearningResourceVersion;
use App\Models\User;
use App\Support\LearningCatalog;
use App\Support\RenameLearningResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RenameLearningResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_rename_preserves_pinned_snapshot_and_files_but_updates_display_name(): void
    {
        $actor = User::factory()->create(['is_admin' => true, 'is_superadmin' => true]);
        $resource = new LearningResource;
        $resource->kind = 'materials';
        $resource->revision = 1;
        $resource->created_by = $actor->id;
        $resource->save();
        $old = new LearningResourceVersion;
        $old->resource_id = $resource->id;
        $old->revision = 1;
        $old->title = 'Modul HSK 1';
        $old->status = 'ready';
        $old->content = ['usage' => 'reference', 'source' => 'pdf'];
        $old->file_path = 'learning/original.pdf';
        $old->file_name = 'original.pdf';
        $old->actor_id = $actor->id;
        $old->created_at = now();
        $old->save();
        RenameLearningResource::run($resource->id, 'Absolute Beginner - Beginner', $actor);
        $new = $resource->versions()->where('revision', 2)->firstOrFail();
        $this->assertSame('Modul HSK 1', $old->fresh()->title);
        $this->assertSame($old->file_path, $new->file_path);
        $this->assertSame($old->content, $new->content);
        $pinned = collect(LearningCatalog::forPrograms())->firstWhere('id', $old->id);
        $this->assertSame('Absolute Beginner - Beginner', $pinned['display_title']);
        $this->assertSame('Modul HSK 1', $pinned['title']);
        RenameLearningResource::run($resource->id, 'Absolute Beginner - Beginner', $actor);
        $this->assertSame(2, $resource->fresh()->revision);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'learning.version.saved', 'subject_id' => $resource->id]);
    }
}
