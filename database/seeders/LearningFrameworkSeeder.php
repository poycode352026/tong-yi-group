<?php

namespace Database\Seeders;

use App\Models\LearningResource;
use App\Models\LearningResourceVersion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LearningFrameworkSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Serialisasi dengan bootstrap/perubahan akun Superadmin dan pemanggilan seed lain.
            DB::select('SELECT pg_advisory_xact_lock(?)', [740071]);
            $actor = User::where('is_admin', true)->where('is_superadmin', true)->orderBy('id')->first();
            if (! $actor) {
                throw new RuntimeException('Buat dan tetapkan akun Superadmin sebelum menjalankan seed acuan. Tidak ada akun yang dibuat otomatis.');
            }
            foreach ($this->definitions() as $definition) {
                if (LearningResource::whereKey($definition['id'])->exists()) {
                    continue;
                }
                $existing = LearningResourceVersion::join('learning_resources as r', function ($join) {
                    $join->on('r.id', '=', 'learning_resource_versions.resource_id')->on('r.revision', '=', 'learning_resource_versions.revision');
                })->where('r.kind', 'frameworks')->whereIn(DB::raw('LOWER(TRIM(title))'), $definition['aliases'])->exists();
                if ($existing) {
                    continue;
                }
                $resource = new LearningResource;
                $resource->id = $definition['id'];
                $resource->kind = 'frameworks';
                $resource->revision = 1;
                $resource->created_by = $actor->id;
                $resource->save();

                $version = new LearningResourceVersion;
                $version->resource_id = $resource->id;
                $version->revision = 1;
                $version->title = $definition['title'];
                $version->status = 'ready';
                $version->content = ['description' => $definition['description'], 'levels' => $definition['levels']];
                $version->actor_id = $actor->id;
                $version->created_at = now();
                $version->save();

                DB::table('admin_audit_logs')->insert([
                    'actor_id' => $actor->id,
                    'target_user_id' => null,
                    'action' => 'learning.framework.seeded',
                    'before' => json_encode([], JSON_THROW_ON_ERROR),
                    'after' => json_encode(['source' => self::class, 'resource_id' => $resource->id, 'version_id' => $version->id, 'title' => $version->title], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                ]);
            }
        });
    }

    private function definitions(): array
    {
        return [
            [
                'id' => '8b7d8dc0-6b70-4a49-9901-4b0f3a1a7a01',
                'title' => 'HSK',
                'aliases' => ['hsk'],
                'description' => 'Acuan pembelajaran bahasa Mandarin dengan tingkatan HSK 1 sampai HSK 6.',
                'levels' => ['HSK 1', 'HSK 2', 'HSK 3', 'HSK 4', 'HSK 5', 'HSK 6'],
            ],
            [
                'id' => '8b7d8dc0-6b70-4a49-9901-4b0f3a1a7a02',
                'title' => 'BCT — Business Chinese Test',
                'aliases' => ['bct', 'business chinese test', 'bct — business chinese test', 'bct business chinese test', 'bct - business chinese test'],
                'description' => "BCT A: Ditujukan untuk pembelajar Mandarin bisnis tingkat pemula hingga dasar.\n\nBCT B: Ditujukan untuk pembelajar tingkat menengah hingga mahir.",
                'levels' => ['BCT A', 'BCT B'],
            ],
        ];
    }
}
