<?php

namespace App\Support;

use App\Models\LearningResourceVersion;

class ModuleGroups
{
    public static function name(LearningResourceVersion $version): string
    {
        if (! empty($version->content['group'])) {
            return $version->content['group'];
        }
        $references = LearningReferences::forVersion($version);
        $frameworks = $references['frameworks'] ?? [];
        if (count($frameworks) === 1) {
            return LearningResourceVersion::find($frameworks[0]['version_id'])?->title ?? '';
        }
        if (! empty($references['internal']) && ! $frameworks) {
            return 'Tong Yi';
        }

        return '';
    }

    public static function options(): array
    {
        return collect(['HSK', 'BCT', 'Tong Yi'])->merge(LearningResourceVersion::whereHas('resource', fn ($q) => $q->where('kind', 'materials'))->get()->map(fn ($v) => self::name($v)))->filter()->unique()->sort()->values()->all();
    }
}
