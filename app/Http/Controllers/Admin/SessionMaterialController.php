<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearningResource;
use App\Models\LearningResourceVersion;
use App\Support\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SessionMaterialController extends Controller
{
    public function store(Request $request)
    {
        abort_unless($request->user()->can('programs.manage'), 403);
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf', 'extensions:pdf', 'max:2048'],
        ], ['file.uploaded' => 'Berkas tidak dapat diunggah. Pastikan PDF tidak melebihi 2 MB.', 'file.max' => 'PDF maksimal 2 MB.', 'file.mimes' => 'Pilih berkas PDF.', 'file.extensions' => 'Ekstensi berkas harus PDF.']);
        $path = $request->file('file')->store('learning', 'learning');
        try {
            $version = DB::transaction(function () use ($request, $data, $path) {
                $resource = new LearningResource;
                $resource->kind = 'materials';
                $resource->revision = 1;
                $resource->created_by = $request->user()->id;
                $resource->save();
                $version = new LearningResourceVersion;
                $version->resource_id = $resource->id;
                $version->revision = 1;
                $version->title = ($data['title'] ?? '') ?: mb_substr(pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME), 0, 255);
                $version->status = 'ready';
                $version->content = ['usage' => 'session', 'source' => 'pdf', 'description' => '', 'url' => null];
                $version->file_path = $path;
                $version->file_name = mb_substr(basename($request->file('file')->getClientOriginalName()), 0, 200);
                $version->actor_id = $request->user()->id;
                $version->created_at = now();
                $version->save();
                ActivityLog::learning($version);

                return $version;
            });
        } catch (Throwable $e) {
            Storage::disk('learning')->delete($path);
            throw $e;
        }

        return response()->json(['id' => $version->id, 'resource_id' => $version->resource_id, 'kind' => 'materials', 'usage' => 'session', 'title' => $version->title, 'revision' => 1, 'preview_url' => '/admin/learning/versions/'.$version->id.'/preview', 'file_url' => '/admin/learning/versions/'.$version->id.'/file', 'url' => null]);
    }
}
