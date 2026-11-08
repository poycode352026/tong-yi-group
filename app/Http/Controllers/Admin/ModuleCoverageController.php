<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveCoverageRequest;
use App\Models\LearningResource;
use App\Support\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ModuleCoverageController extends Controller
{
    private function present(LearningResource $resource): array
    {
        $version = $resource->versions()->where('revision', $resource->revision)->firstOrFail();

        return ['id' => $resource->id, 'revision' => $resource->revision, 'title' => $version->title, 'description' => $version->content['description'] ?? '', 'levels' => $version->content['levels'] ?? [], 'archived' => $resource->archived_at !== null];
    }

    public function index()
    {
        return Inertia::render('Admin/Learning/Coverage', ['coverage' => LearningResource::where('kind', 'frameworks')->get()->map(fn ($resource) => $this->present($resource))->sortBy('title')->values()]);
    }

    public function store(SaveCoverageRequest $request)
    {
        $request->headers->set('Accept', 'application/json');
        $response = app(LearningResourceController::class)->store($request, 'frameworks');
        $id = $response->getData()->resource_id;

        return response()->json($this->present(LearningResource::findOrFail($id)));
    }

    public function update(SaveCoverageRequest $request, LearningResource $resource)
    {
        abort_unless($resource->kind === 'frameworks', 404);
        abort_if($resource->archived_at !== null, 409, 'Pulihkan cakupan sebelum mengubahnya.');
        $request->headers->set('Accept', 'application/json');
        app(LearningResourceController::class)->update($request, 'frameworks', $resource);

        return response()->json($this->present($resource->fresh()));
    }

    public function archive(Request $request, LearningResource $resource)
    {
        return $this->setArchive($request, $resource, true);
    }

    public function restore(Request $request, LearningResource $resource)
    {
        return $this->setArchive($request, $resource, false);
    }

    private function setArchive(Request $request, LearningResource $resource, bool $archive)
    {
        abort_unless($resource->kind === 'frameworks', 404);
        $data = $request->validate(['revision' => ['required', 'integer', 'min:1']]);
        $resource = DB::transaction(function () use ($request, $resource, $data, $archive) {
            $resource = LearningResource::whereKey($resource->id)->lockForUpdate()->firstOrFail();
            abort_unless($resource->revision === (int) $data['revision'], 409, 'Cakupan telah berubah. Muat ulang sebelum mencoba kembali.');
            if (($resource->archived_at !== null) === $archive) {
                return $resource;
            }
            $old = $resource->versions()->where('revision', $resource->revision)->firstOrFail();
            $version = $old->replicate(['id', 'created_at']);
            $version->revision = ++$resource->revision;
            $version->actor_id = $request->user()->id;
            $version->created_at = now();
            $resource->archived_at = $archive ? now() : null;
            $resource->save();
            $version->save();
            ActivityLog::record($request->user()->id, $archive ? 'coverage.archived' : 'coverage.restored', ($archive ? 'Cakupan dihapus dari pilihan: ' : 'Cakupan dipulihkan: ').$old->title, ['title' => $old->title, 'revision' => $old->revision, 'archived' => ! $archive], ['title' => $version->title, 'revision' => $version->revision, 'archived' => $archive], 'frameworks', $resource->id, 'learning:'.$version->id);

            return $resource;
        });

        return response()->json($this->present($resource));
    }
}
