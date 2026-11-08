<?php

namespace App\Support;

use App\Models\LearningResourceVersion;

class LearningCatalog
{
    // Hanya metadata aman; kunci jawaban/path penyimpanan tidak dikirim ke editor program.
    public static function forPrograms(): array
    {
        $moduleTitles = LearningResourceVersion::query()->join('learning_resources as r', function ($join) {
            $join->on('r.id', '=', 'learning_resource_versions.resource_id')->on('r.revision', '=', 'learning_resource_versions.revision');
        })->where('r.kind', 'materials')->pluck('title', 'resource_id');

        return LearningResourceVersion::with('resource:id,kind,revision')->where('status', 'ready')->orderBy('title')->get()
            ->map(fn ($v) => ['id' => $v->id, 'kind' => $v->resource->kind, 'is_current' => $v->revision === $v->resource->revision, 'questions_count' => $v->resource->kind === 'assessments' ? count($v->content['questions'] ?? []) : null, 'resource_id' => $v->resource_id, 'usage' => $v->content['usage'] ?? 'legacy', 'group' => $v->resource->kind === 'materials' ? ModuleGroups::name($v) : null, 'title' => $v->title, 'display_title' => $v->resource->kind === 'materials' ? ($moduleTitles[$v->resource_id] ?? $v->title) : $v->title, 'revision' => $v->revision,
                'attachments' => $v->resource->kind === 'materials' ? ModuleFiles::present($v) : [],
                'framework_version_id' => $v->content['framework_version_id'] ?? null, 'framework_level' => $v->content['framework_level'] ?? null,
                'edition' => $v->content['edition'] ?? '', 'levels' => $v->content['levels'] ?? [],
                'level_target' => $v->content['level_target'] ?? null, 'reference_labels' => LearningReferences::labels(LearningReferences::forVersion($v)), 'scope' => $v->content['scope'] ?? null, 'target' => $v->content['target'] ?? null,
                'preview_url' => $v->file_path ? '/admin/learning/versions/'.$v->id.'/preview' : null,
                'file_url' => $v->file_path ? '/admin/learning/versions/'.$v->id.'/file' : null,
                'url' => $v->resource->kind === 'materials' ? ($v->content['url'] ?? null) : null])->all();
    }
}
