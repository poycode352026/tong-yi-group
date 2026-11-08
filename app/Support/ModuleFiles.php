<?php

namespace App\Support;

use App\Models\LearningResourceFile;
use App\Models\LearningResourceVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ModuleFiles
{
    public static function present(LearningResourceVersion $version): array
    {
        $attachments = $version->content['attachments'] ?? [];
        if (! $attachments && ($version->file_path || ($version->content['url'] ?? null))) {
            return [['id' => $version->id, 'title' => $version->title, 'purpose' => '', 'description' => '', 'source' => $version->file_path ? 'pdf' : 'link',
                'url' => $version->content['url'] ?? null, 'file_id' => null, 'legacy_version_id' => $version->id, 'file_name' => $version->file_name,
                'references' => null, 'reference_labels' => LearningReferences::labels(LearningReferences::forVersion($version)),
                'file_url' => $version->file_path ? '/admin/learning/versions/'.$version->id.'/file' : null, 'preview_url' => $version->file_path ? '/admin/learning/versions/'.$version->id.'/preview' : null]];
        }

        return array_map(fn ($item) => [...$item,
            'reference_labels' => LearningReferences::labels($item['references'] ?? LearningReferences::forVersion($version)),
            'file_name' => $item['file_name'] ?? (isset($item['file_id']) ? LearningResourceFile::find($item['file_id'])?->file_name : (isset($item['legacy_version_id']) ? LearningResourceVersion::find($item['legacy_version_id'])?->file_name : null)),
            'file_url' => $item['source'] === 'pdf' ? '/admin/learning/versions/'.$version->id.'/attachments/'.$item['id'].'/file' : null,
            'preview_url' => $item['source'] === 'pdf' ? '/admin/learning/versions/'.$version->id.'/attachments/'.$item['id'].'/preview' : null,
        ], $attachments);
    }

    public static function identicalUpload(UploadedFile $file, ?string $path, ?string $name): bool
    {
        return $path && $name === mb_substr(basename($file->getClientOriginalName()), 0, 200)
            && Storage::disk('learning')->exists($path)
            && hash_file('sha256', $file->getRealPath()) === hash('sha256', Storage::disk('learning')->get($path));
    }

    public static function save(array $items, string $resourceId, int $actorId, array &$uploaded): array
    {
        $previous = LearningResourceVersion::where('resource_id', $resourceId)->orderByDesc('revision')->first();
        $oldRows = collect($previous?->content['attachments'] ?? [])->keyBy('id');
        $result = [];
        ksort($items, SORT_NUMERIC);
        foreach ($items as $index => $item) {
            $old = $oldRows->get($item['id']);
            if (! empty($item['file']) && $old && $item['source'] === 'pdf') {
                $oldFile = ! empty($old['file_id']) ? LearningResourceFile::whereKey($old['file_id'])->where('resource_id', $resourceId)->first() : null;
                $legacy = ! empty($old['legacy_version_id']) ? LearningResourceVersion::whereKey($old['legacy_version_id'])->where('resource_id', $resourceId)->first() : null;
                $stored = $oldFile ?? $legacy;
                if ($stored && self::identicalUpload($item['file'], $stored->file_path, $stored->file_name)) {
                    $item['file'] = null;
                    $item['file_id'] = $old['file_id'] ?? null;
                    $item['legacy_version_id'] = $old['legacy_version_id'] ?? null;
                }
            }
            $row = array_intersect_key($item, array_flip(['id', 'title', 'purpose', 'description', 'source', 'url', 'file_id', 'legacy_version_id', 'references']));
            if (isset($row['references'])) {
                $row['references'] = LearningReferences::normalize($row['references']);
            }
            if ($item['source'] === 'link') {
                $row['file_id'] = null;
                $row['legacy_version_id'] = null;
            } elseif (! empty($item['file'])) {
                $path = $item['file']->store('learning', 'learning');
                $uploaded[] = $path;
                $file = new LearningResourceFile;
                $file->resource_id = $resourceId;
                $file->file_path = $path;
                $file->file_name = mb_substr(basename($item['file']->getClientOriginalName()), 0, 200);
                $file->actor_id = $actorId;
                $file->created_at = now();
                $file->save();
                $row['file_name'] = $file->file_name;
                $row['file_id'] = $file->id;
                $row['legacy_version_id'] = null;
                $row['url'] = null;
            } elseif (! empty($item['file_id'])) {
                $existing = LearningResourceFile::whereKey($item['file_id'])->where('resource_id', $resourceId)->first();
                if (! $existing) {
                    throw ValidationException::withMessages(["attachments.$index.file" => 'Berkas tidak berasal dari modul ini.']);
                }
                $row['file_name'] = $existing->file_name;
                $row['url'] = null;
            } elseif (! empty($item['legacy_version_id'])) {
                $legacy = LearningResourceVersion::whereKey($item['legacy_version_id'])->where('resource_id', $resourceId)->first();
                if (! $legacy?->file_path) {
                    throw ValidationException::withMessages(["attachments.$index.file" => 'Berkas lama tidak tersedia pada modul ini.']);
                }
                $row['file_name'] = $legacy->file_name;
                $row['url'] = null;
            } else {
                throw ValidationException::withMessages(["attachments.$index.file" => 'Pilih PDF untuk berkas ini.']);
            }
            $result[] = $row;
        }

        return $result;
    }
}
