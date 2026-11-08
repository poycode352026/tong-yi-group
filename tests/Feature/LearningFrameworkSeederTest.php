<?php

namespace Tests\Feature;

use App\Models\LearningResource;
use App\Models\LearningResourceVersion;
use App\Models\User;
use Database\Seeders\LearningFrameworkSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class LearningFrameworkSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_is_repeatable_and_contains_requested_ready_levels(): void
    {
        $root = User::factory()->create(['is_admin' => true, 'is_superadmin' => true]);
        $this->seed(LearningFrameworkSeeder::class);
        $this->seed(LearningFrameworkSeeder::class);
        $this->assertDatabaseCount('learning_resources', 2);
        $this->assertDatabaseCount('learning_resource_versions', 2);
        $this->assertDatabaseCount('admin_audit_logs', 2);
        $hsk = LearningResourceVersion::where('title', 'HSK')->firstOrFail();
        $bct = LearningResourceVersion::where('title', 'BCT — Business Chinese Test')->firstOrFail();
        $this->assertSame(['HSK 1', 'HSK 2', 'HSK 3', 'HSK 4', 'HSK 5', 'HSK 6'], $hsk->content['levels']);
        $this->assertSame(['BCT A', 'BCT B'], $bct->content['levels']);
        $this->assertStringContainsString('pemula hingga dasar', $bct->content['description']);
        $this->assertStringContainsString('menengah hingga mahir', $bct->content['description']);
        $this->assertSame('ready', $hsk->status);
        $this->assertSame('ready', $bct->status);
        $this->assertSame($root->id, $bct->actor_id);
    }

    public function test_existing_user_content_and_seeded_edits_are_not_overwritten(): void
    {
        $root = User::factory()->create(['is_admin' => true, 'is_superadmin' => true]);
        $resource = new LearningResource;
        $resource->id = (string) Str::uuid();
        $resource->kind = 'frameworks';
        $resource->revision = 1;
        $resource->created_by = $root->id;
        $resource->save();
        $version = new LearningResourceVersion;
        $version->resource_id = $resource->id;
        $version->revision = 1;
        $version->title = 'HSK';
        $version->status = 'ready';
        $version->content = ['description' => 'Keterangan pengguna dipertahankan.', 'levels' => ['HSK 1', 'HSK 2', 'HSK 3', 'HSK 4', 'HSK 5', 'HSK 6']];
        $version->actor_id = $root->id;
        $version->created_at = now();
        $version->save();
        $this->seed(LearningFrameworkSeeder::class);
        $this->assertDatabaseCount('learning_resources', 2);
        $this->assertSame('Keterangan pengguna dipertahankan.', $version->fresh()->content['description']);
        // Seed mengenali identitas stabil meskipun nama bawaan kemudian diubah Admin.
        $bct = LearningResourceVersion::where('title', 'BCT — Business Chinese Test')->firstOrFail();
        $bct->title = 'BCT yang disesuaikan';
        $bct->save();
        $this->seed(LearningFrameworkSeeder::class);
        $this->assertDatabaseCount('learning_resource_versions', 2);
        $this->assertSame('BCT yang disesuaikan', $bct->fresh()->title);
    }

    public function test_missing_superadmin_does_not_create_accounts_or_partial_resources(): void
    {
        try {
            $this->seed(LearningFrameworkSeeder::class);
            $this->fail('Seed harus meminta akun Superadmin yang sudah ada.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Superadmin', $exception->getMessage());
        }
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('learning_resources', 0);
    }
}
