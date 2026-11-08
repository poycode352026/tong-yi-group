<?php

namespace App\Support;

use App\Models\LearningResource;
use App\Models\LearningResourceVersion;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class LearningReferences
{
    public static function rules(string $prefix, bool $required = false): array
    {
        return [
            $prefix => [$required ? 'required' : 'nullable', 'array:internal,frameworks'],
            "$prefix.internal" => ['sometimes', 'boolean'],
            "$prefix.frameworks" => ['sometimes', 'array', 'max:30'],
            "$prefix.frameworks.*" => ['array:version_id,levels'],
            "$prefix.frameworks.*.version_id" => ['required', 'uuid', Rule::exists('learning_resource_versions', 'id')->where('status', 'ready')->whereIn('resource_id', LearningResource::select('id')->where('kind', 'frameworks'))],
            "$prefix.frameworks.*.levels" => ['present', 'array', 'max:100'],
            "$prefix.frameworks.*.levels.*" => ['required', 'string', 'max:100'],
        ];
    }

    public static function validate(?array $references, string $prefix, Validator $validator, ?array $previous = null): void
    {
        if ($references === null) {
            return;
        }
        if (empty($references['internal']) && empty($references['frameworks'])) {
            $validator->errors()->add($prefix, 'Pilih acuan pembelajaran atau Internal Tong Yi.');
        }
        foreach ($references['frameworks'] ?? [] as $i => $mapping) {
            $version = LearningResourceVersion::find($mapping['version_id']);
            if ($version?->resource->archived_at !== null) {
                $old = collect($previous['frameworks'] ?? [])->firstWhere('version_id', $mapping['version_id']);
                $oldLevels = $old['levels'] ?? [];
                $nextLevels = $mapping['levels'];
                sort($oldLevels);
                sort($nextLevels);
                if (! $old || $oldLevels !== $nextLevels) {
                    $validator->errors()->add("$prefix.frameworks.$i.version_id", 'Cakupan ini sudah dihapus dari pilihan baru.');
                }
            }
            if (count($mapping['levels']) !== count(array_unique($mapping['levels']))) {
                $validator->errors()->add("$prefix.frameworks.$i.levels", 'Tingkatan tidak boleh berulang dalam satu acuan.');
            }
            if (array_diff($mapping['levels'], $version?->content['levels'] ?? [])) {
                $validator->errors()->add("$prefix.frameworks.$i.levels", 'Tingkatan harus berasal dari acuan yang dipilih.');
            }
        }
    }

    public static function normalize(array $references): array
    {
        $frameworks = array_map(function ($mapping) {
            $levels = array_values($mapping['levels']);
            sort($levels, SORT_STRING);

            return ['version_id' => $mapping['version_id'], 'levels' => $levels];
        }, $references['frameworks'] ?? []);
        usort($frameworks, fn ($a, $b) => strcmp($a['version_id'], $b['version_id']));

        return ['internal' => (bool) ($references['internal'] ?? false), 'frameworks' => $frameworks];
    }

    public static function forVersion(LearningResourceVersion $version): ?array
    {
        if (isset($version->content['references'])) {
            return $version->content['references'];
        }
        if (isset($version->content['legacy_metadata']['references'])) {
            return $version->content['legacy_metadata']['references'];
        }
        $metadata = $version->content['legacy_metadata'] ?? $version->content;
        if (! empty($metadata['framework_version_id'])) {
            return ['internal' => false, 'frameworks' => [['version_id' => $metadata['framework_version_id'], 'levels' => empty($metadata['framework_level']) ? [] : [$metadata['framework_level']]]]];
        }

        return null;
    }

    public static function labels(?array $references): array
    {
        $labels = ! empty($references['internal']) ? ['Internal Tong Yi'] : [];
        foreach ($references['frameworks'] ?? [] as $mapping) {
            $version = LearningResourceVersion::find($mapping['version_id']);
            if ($version) {
                $labels[] = $version->title.(empty($mapping['levels']) ? '' : ': '.implode(', ', $mapping['levels']));
            }
        }

        return $labels;
    }
}
