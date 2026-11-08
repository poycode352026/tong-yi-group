<?php

namespace App\Support;

use App\Models\LearningResourceVersion;
use Illuminate\Support\Facades\DB;

class ActivityLog
{
    public static function record(?int $actor, string $action, string $summary, array $before = [], array $after = [], ?string $type = null, ?string $id = null, ?string $source = null, string $outcome = 'success'): void
    {
        DB::table('admin_audit_logs')->insert([
            'actor_id' => $actor, 'target_user_id' => null, 'action' => $action, 'summary' => mb_substr($summary, 0, 255),
            'subject_type' => $type, 'subject_id' => $id, 'source_key' => $source, 'outcome' => $outcome,
            'before' => json_encode(self::sanitize($before), JSON_THROW_ON_ERROR), 'after' => json_encode(self::sanitize($after), JSON_THROW_ON_ERROR), 'created_at' => now(),
        ]);
    }

    public static function sanitize(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), ['password', 'password_confirmation', 'current_password', 'new_password', 'remember_token', '_token', 'token', 'access_token', 'refresh_token', 'authorization', 'cookie', 'file_path', 'answer', 'guide'])) {
                continue;
            }
            $result[$key] = is_array($value) ? self::sanitize($value) : $value;
        }

        return $result;
    }

    public static function programLevels(int $actor, array $before, array $after, string $programId): void
    {
        $archive = function (array $content): array {
            $items = [];
            foreach ($content['subprograms'] ?? [] as $sub) {
                foreach ($sub['archived_levels'] ?? [] as $level) {
                    $items[$level['id']] = ['program_name' => $content['name'] ?? '', 'subprogram_name' => $sub['name'] ?? '', 'level_name' => $level['name'] ?: 'Tanpa nama', 'level_id' => $level['id']];
                }
            }

            return $items;
        };
        $old = $archive($before);
        $new = $archive($after);
        foreach (array_diff_key($new, $old) as $item) {
            self::record($actor, 'level.archived', 'Level diarsipkan: '.$item['level_name'], [...$item, 'state' => 'active'], [...$item, 'state' => 'archived'], 'program', $programId);
        }
        foreach (array_diff_key($old, $new) as $item) {
            self::record($actor, 'level.restored', 'Level dikembalikan: '.$item['level_name'], [...$item, 'state' => 'archived'], [...$item, 'state' => 'active'], 'program', $programId);
        }
    }

    public static function learning(LearningResourceVersion $version, ?LearningResourceVersion $previous = null): void
    {
        $snapshot = fn ($v) => $v ? ['title' => $v->title, 'revision' => $v->revision, 'status' => $v->status,
            'version_id' => $v->id, 'group' => $v->content['group'] ?? null, 'levels' => $v->content['levels'] ?? [], 'scope' => $v->content['scope'] ?? null,
            'references' => $v->content['references'] ?? null, 'attachment_references' => array_map(fn ($row) => ['id' => $row['id'], 'title' => $row['title'], 'references' => $row['references'] ?? null], $v->content['attachments'] ?? []),
            'usage' => $v->content['usage'] ?? null, 'file_name' => $v->file_name, 'framework_version_id' => $v->content['framework_version_id'] ?? null, 'framework_level' => $v->content['framework_level'] ?? null, 'attachments_count' => count($v->content['attachments'] ?? []), 'questions_count' => count($v->content['questions'] ?? []), 'question_media' => array_map(fn ($q) => ['id' => $q['id'], 'files' => array_map(fn ($media) => $media['name'], $q['media'] ?? [])], $v->content['questions'] ?? [])] : [];
        self::record($version->actor_id, 'learning.version.saved', 'Sumber belajar disimpan: '.$version->title,
            $snapshot($previous), $snapshot($version), $version->resource->kind, $version->resource_id, 'learning:'.$version->id);
    }
}
