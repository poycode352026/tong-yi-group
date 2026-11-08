<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveLearningResourceRequest;
use App\Models\LearningResource;
use App\Models\LearningResourceFile;
use App\Models\LearningResourceVersion;
use App\Support\ActivityLog;
use App\Support\ContentComparison;
use App\Support\ExamMedia;
use App\Support\ExamPlacement;
use App\Support\LearningCatalog;
use App\Support\LearningReferences;
use App\Support\LevelModulePlacement;
use App\Support\ModuleFiles;
use App\Support\ModuleGroups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class LearningResourceController extends Controller
{
    public function index(Request $request, string $kind)
    {
        $search = mb_substr(trim((string) $request->query('search')), 0, 100);
        $query = LearningResourceVersion::query()->join('learning_resources as r', function ($join) {
            $join->on('r.id', '=', 'learning_resource_versions.resource_id')->on('r.revision', '=', 'learning_resource_versions.revision');
        })->where('r.kind', $kind)->select('learning_resource_versions.*')->orderByDesc('learning_resource_versions.created_at');
        if ($kind === 'materials') {
            $query->whereRaw("COALESCE(content->>'usage', 'reference') = 'reference'");
        }
        $group = $kind === 'materials' ? mb_substr(trim((string) $request->query('group')), 0, 100) : '';
        if ($group !== '') {
            $ids = (clone $query)->get()->filter(fn ($version) => ModuleGroups::name($version) === $group)->pluck('id');
            $query->whereIn('learning_resource_versions.id', $ids);
        }
        if ($search !== '') {
            $query->where('title', 'ilike', '%'.$search.'%');
        }

        return Inertia::render('Admin/Learning/Index', ['kind' => $kind, 'search' => $search, 'group' => $group,
            'resources' => $query->paginate(15)->withQueryString()->through(fn ($v) => ['id' => $v->resource_id, 'title' => $v->title, 'group' => $kind === 'materials' ? ModuleGroups::name($v) : null, 'status' => $v->status, 'revision' => $v->revision, 'target' => $v->content['target'] ?? null, 'level_target' => $v->content['level_target'] ?? null, 'reference_labels' => LearningReferences::labels(LearningReferences::forVersion($v)), 'edition' => $v->content['edition'] ?? '', 'framework_level' => $v->content['framework_level'] ?? null, 'attachments_count' => isset($v->content['attachments']) ? count($v->content['attachments']) : (($v->file_path || ($v->content['url'] ?? null)) ? 1 : 0), 'updated_at' => $v->created_at->toIso8601String()])]);
    }

    public function catalog()
    {
        return response()->json(LearningCatalog::forPrograms())->header('Cache-Control', 'private, no-store');
    }

    public function create(Request $request, string $kind)
    {
        return Inertia::render('Admin/Learning/Edit', ['kind' => $kind, 'resource' => null, 'moduleContext' => $request->only(['program', 'subprogram', 'level']), 'versions' => [], 'examPrograms' => $kind === 'assessments' ? ExamPlacement::options() : [], 'frameworkOptions' => $this->frameworkOptions($kind)]);
    }

    public function edit(string $kind, LearningResource $resource)
    {
        abort_unless($resource->kind === $kind, 404);
        $version = $resource->versions()->where('revision', $resource->revision)->firstOrFail();

        return Inertia::render('Admin/Learning/Edit', ['kind' => $kind, 'resource' => [
            'id' => $resource->id, 'revision' => $resource->revision, 'title' => $version->title, 'status' => $version->status,
            ...$version->content, ...($kind === 'assessments' ? ['questions' => ExamMedia::present($version)] : []), 'references' => LearningReferences::forVersion($version), 'group' => ModuleGroups::name($version), 'attachments' => $kind === 'materials' ? ModuleFiles::present($version) : [], 'file_name' => $version->file_name,
        ], 'examPrograms' => $kind === 'assessments' ? ExamPlacement::options() : [], 'frameworkOptions' => $this->frameworkOptions($kind), 'versions' => $resource->versions()->orderByDesc('revision')->get()->map(fn ($v) => ['id' => $v->id, 'revision' => $v->revision, 'title' => $v->title, 'group' => $kind === 'materials' ? ModuleGroups::name($v) : null, 'status' => $v->status, 'created_at' => $v->created_at->toIso8601String(), 'file_name' => $v->file_name])]);
    }

    public function store(SaveLearningResourceRequest $request, string $kind)
    {
        return $this->save($request, $kind);
    }

    public function update(SaveLearningResourceRequest $request, string $kind, LearningResource $resource)
    {
        abort_unless($resource->kind === $kind, 404);

        return $this->save($request, $kind, $resource);
    }

    private function save(SaveLearningResourceRequest $request, string $kind, ?LearningResource $resource = null)
    {
        $uploaded = [];
        $changed = false;
        try {
            $resource = DB::transaction(function () use ($request, $kind, $resource, &$uploaded, &$changed) {
                $previous = null;
                if ($resource) {
                    $resource = LearningResource::whereKey($resource->id)->lockForUpdate()->firstOrFail();
                    if ($resource->revision !== $request->integer('revision')) {
                        throw ValidationException::withMessages(['revision' => 'Data telah berubah. Salin isian Anda sebelum memuat ulang.']);
                    }
                    $previous = $resource->versions()->where('revision', $resource->revision)->firstOrFail();
                    abort_if($kind === 'frameworks' && $resource->archived_at !== null, 409, 'Pulihkan cakupan sebelum mengubahnya.');
                } else {
                    abort_unless($request->integer('revision') === 0, 409);
                    $resource = new LearningResource;
                    $resource->kind = $kind;
                    $resource->created_by = $request->user()->id;
                    $resource->revision = 0;
                }
                $path = $kind === 'materials' && $request->input('source') === 'pdf' ? $previous?->file_path : null;
                $name = $path ? $previous->file_name : null;
                if ($request->hasFile('file')) {
                    if ($kind !== 'materials' || $request->input('source') !== 'pdf') {
                        throw ValidationException::withMessages(['file' => 'Unggah PDF hanya untuk sumber PDF pada Pustaka Guru.']);
                    }
                    if (! ModuleFiles::identicalUpload($request->file('file'), $path, $name)) {
                        $path = $request->file('file')->store('learning', 'learning');
                        $uploaded[] = $path;
                        abort_unless($path, 500, 'Berkas tidak dapat disimpan.');
                        $name = mb_substr(basename($request->file('file')->getClientOriginalName()), 0, 200);
                    }
                }
                if ($kind === 'materials' && $request->input('source') === 'pdf' && $request->input('status') === 'ready' && ! in_array($request->input('format'), ['module', 'module_level', 'module_catalog']) && ! $path) {
                    throw ValidationException::withMessages(['file' => 'Unggah PDF sebelum versi siap dipakai.']);
                }
                $data = $request->validated();
                $content = array_intersect_key($data, array_flip(match ($kind) {
                    'frameworks' => ['description', 'edition', 'levels'],
                    'materials' => ['description', 'edition', 'source', 'url', 'author', 'usage'],
                    'assessments' => ['description', 'edition', 'scope', 'questions'],
                }));
                if ($kind === 'assessments') {
                    $content['questions'] = ExamMedia::save($data['questions'], $previous, $uploaded);
                }
                if ($kind === 'assessments' && ! empty($data['target'])) {
                    $content['target'] = ExamPlacement::resolve($data['target'], $data['scope']);
                }
                if ($kind === 'materials') {
                    $content['usage'] = $data['usage'] ?? $previous?->content['usage'] ?? 'reference';
                }
                if ($kind === 'materials' && ($content['source'] ?? null) === 'pdf') {
                    $content['url'] = null;
                }
                if (! $resource->exists) {
                    $resource->save();
                }
                if ($kind === 'materials' && in_array($data['format'] ?? null, ['module', 'module_level', 'module_catalog'])) {
                    $content = array_intersect_key($data, array_flip(['description', 'author', 'framework_version_id', 'framework_level', 'format']));
                    $content['usage'] = 'reference';
                    $content['attachments'] = ModuleFiles::save($data['attachments'], $resource->id, $request->user()->id, $uploaded);
                    if ($data['format'] === 'module_catalog') {
                        $content = array_intersect_key($content, array_flip(['description', 'author', 'format', 'usage', 'attachments']));
                        if (! empty($data['group'])) {
                            $content['group'] = trim($data['group']);
                        } elseif (isset($previous?->content['group'])) {
                            $content['group'] = $previous->content['group'];
                        }
                        if (isset($data['references'])) {
                            $references = LearningReferences::normalize($data['references']);
                            $previousReferences = $previous ? LearningReferences::forVersion($previous) : null;
                            if (isset($previous?->content['references']) || ! ContentComparison::same($references, LearningReferences::normalize($previousReferences ?? []))) {
                                $content['references'] = $references;
                            }
                        }
                        $legacy = $previous?->content['legacy_metadata'] ?? (($previous?->content['format'] ?? null) !== 'module_catalog' ? array_intersect_key($previous?->content ?? [], array_flip(['references', 'level_target', 'framework_version_id', 'framework_level', 'edition'])) : []);
                        if ($legacy) {
                            $content['legacy_metadata'] = $legacy;
                        }
                    } elseif ($data['format'] === 'module_level') {
                        $content = array_intersect_key($content, array_flip(['description', 'author', 'format', 'usage', 'attachments']));
                        $content['level_target'] = LevelModulePlacement::resolve($data['level_target']);
                        $content['references'] = LearningReferences::normalize($data['references']);
                    } else {
                        $data['title'] = 'Modul '.$data['framework_level'];
                    }
                    $path = null;
                    $name = null;
                }
                if ($previous && $previous->title === $data['title'] && $previous->status === $data['status']
                    && ContentComparison::same($previous->content, $content) && $previous->file_path === $path && $previous->file_name === $name) {
                    return $resource;
                }
                $changed = true;
                $resource->revision++;
                $resource->save();
                $version = new LearningResourceVersion;
                $version->resource_id = $resource->id;
                $version->revision = $resource->revision;
                $version->title = $data['title'];
                $version->status = $data['status'];
                $version->content = $content;
                $version->file_path = $path;
                $version->file_name = $name;
                $version->actor_id = $request->user()->id;
                $version->created_at = now();
                $version->save();
                ActivityLog::learning($version, $previous);

                return $resource;
            });
        } catch (Throwable $e) {
            if ($uploaded) {
                Storage::disk('learning')->delete($uploaded);
            }
            throw $e;
        }

        if ($request->expectsJson()) {
            return response()->json(collect(LearningCatalog::forPrograms())->first(fn ($item) => $item['resource_id'] === $resource->id && $item['revision'] === $resource->revision));
        }

        return to_route('admin.learning.edit', ['kind' => $kind, 'resource' => $resource->id])->with('success', $changed ? 'Versi baru tersimpan. Pilihan dalam kurikulum sebelumnya tetap memakai versi lama.' : 'Tidak ada perubahan. Versi tetap sama.');
    }

    private function frameworkOptions(string $kind): array
    {
        if ($kind !== 'materials') {
            return [];
        }

        return LearningResourceVersion::with('resource:id,archived_at')->where('status', 'ready')->whereHas('resource', fn ($q) => $q->where('kind', 'frameworks'))->orderBy('title')->get()->map(fn ($v) => ['id' => $v->id, 'resource_id' => $v->resource_id, 'title' => $v->title, 'revision' => $v->revision, 'archived' => $v->resource->archived_at !== null, 'levels' => $v->content['levels'] ?? []])->all();
    }

    public function questionMedia(Request $request, LearningResourceVersion $version, string $question, string $kind)
    {
        abort_unless($request->user()->can('learning.manage'), 403);
        abort_unless($version->resource->kind === 'assessments' && in_array($kind, ['image', 'audio']), 404);
        $row = collect($version->content['questions'] ?? [])->firstWhere('id', $question);
        $media = $row['media'][$kind] ?? null;
        abort_unless($media && Storage::disk('learning')->exists($media['file_path']), 404);

        return Storage::disk('learning')->response($media['file_path'], $media['name'], ['Content-Type' => $media['mime'], 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'], 'inline');
    }

    public function file(Request $request, LearningResourceVersion $version)
    {
        return $this->pdfResponse($request, $version, false);
    }

    public function preview(Request $request, LearningResourceVersion $version)
    {
        return $this->pdfResponse($request, $version, true);
    }

    public function attachmentFile(Request $request, LearningResourceVersion $version, string $attachment)
    {
        $row = collect($version->content['attachments'] ?? [])->firstWhere('id', $attachment);
        abort_unless($row && $row['source'] === 'pdf', 404);

        return $this->pdfResponse($request, $version, $request->routeIs('admin.learning.attachment.preview'), $row);
    }

    private function pdfResponse(Request $request, LearningResourceVersion $version, bool $preview, ?array $attachment = null)
    {
        abort_unless($request->user()->can('learning.manage') || ($request->user()->can('programs.manage') && $version->status === 'ready'), 403);
        $path = $version->file_path;
        $name = $version->file_name;
        if ($attachment) {
            if (! empty($attachment['file_id'])) {
                $file = LearningResourceFile::whereKey($attachment['file_id'])->where('resource_id', $version->resource_id)->firstOrFail();
                $path = $file->file_path;
                $name = $file->file_name;
            } elseif (! empty($attachment['legacy_version_id'])) {
                $legacy = LearningResourceVersion::whereKey($attachment['legacy_version_id'])->where('resource_id', $version->resource_id)->firstOrFail();
                $path = $legacy->file_path;
                $name = $legacy->file_name;
            } else {
                abort(404);
            }
        }
        abort_unless($version->resource->kind === 'materials' && $path, 404);
        $disk = Storage::disk('learning');
        abort_unless($disk->exists($path), 404);
        $headers = ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store', 'X-Frame-Options' => 'SAMEORIGIN', 'Content-Security-Policy' => "frame-ancestors 'self'"];

        $displayTitle = $attachment ? $version->title.' / '.$attachment['title'] : $version->title;
        ActivityLog::record($request->user()->id, $preview ? 'file.previewed' : 'file.downloaded', $preview ? 'PDF dibuka: '.$displayTitle : 'PDF diunduh: '.$displayTitle, [], ['version_id' => $version->id, 'title' => $attachment['title'] ?? $version->title, 'attachment_id' => $attachment['id'] ?? null], 'materials', $version->resource_id);

        return $preview ? $disk->response($path, $name, $headers, 'inline')
            : $disk->download($path, $name, $headers);
    }

    public function version(LearningResourceVersion $version)
    {
        return Inertia::render('Admin/Learning/Version', ['version' => ['id' => $version->id, 'kind' => $version->resource->kind, 'resource_id' => $version->resource_id, 'revision' => $version->revision, 'title' => $version->title, 'status' => $version->status, 'content' => $version->resource->kind === 'assessments' ? [...$version->content, 'questions' => ExamMedia::present($version)] : $version->content, 'reference_labels' => LearningReferences::labels(LearningReferences::forVersion($version)), 'attachments' => $version->resource->kind === 'materials' ? ModuleFiles::present($version) : [], 'file_name' => $version->file_name]]);
    }
}
