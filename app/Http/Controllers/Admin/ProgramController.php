<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveProgramRequest;
use App\Models\AcademicProgram;
use App\Support\ActivityLog;
use App\Support\ContentComparison;
use App\Support\LearningCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProgramController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AcademicProgram::query()->orderByDesc('updated_at');
        $search = mb_substr(trim((string) $request->query('search')), 0, 100);
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->whereRaw("draft->>'name' ILIKE ?", ['%'.$search.'%'])
                    ->orWhereRaw("draft->>'category' ILIKE ?", ['%'.$search.'%']);
            });
        }

        return Inertia::render('Admin/Programs/Index', [
            'programs' => $query->paginate(15)->withQueryString()->through(fn ($program) => [
                'id' => $program->id,
                'name' => $program->draft['name'] ?: 'Program tanpa judul',
                'category' => $program->draft['category'],
                'subprogram_count' => count($program->draft['subprograms']),
                'published' => $program->published !== null,
                'has_changes' => $program->draft !== $program->published,
                'updated_at' => $program->updated_at->toIso8601String(),
            ]),
            'search' => $search,
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/Programs/Edit', ['program' => null, 'learningCatalog' => LearningCatalog::forPrograms(), 'editorId' => $request->user()->id]);
    }

    public function edit(Request $request, AcademicProgram $program): Response
    {
        return Inertia::render('Admin/Programs/Edit', ['program' => [
            'id' => $program->id,
            'content' => $program->draft,
            'revision' => $program->revision,
            'published_at' => $program->published_at?->toIso8601String(),
        ], 'learningCatalog' => LearningCatalog::forPrograms(), 'editorId' => $request->user()->id]);
    }

    public function store(SaveProgramRequest $request): JsonResponse
    {
        abort_unless($request->integer('revision') === 0, 409, 'Versi data tidak sesuai.');

        return $this->save($request);
    }

    public function update(SaveProgramRequest $request, AcademicProgram $program): JsonResponse
    {
        return $this->save($request, $program);
    }

    private function save(SaveProgramRequest $request, ?AcademicProgram $program = null): JsonResponse
    {
        $program = DB::transaction(function () use ($request, $program) {
            if (! $program) {
                $existing = AcademicProgram::whereKey($request->input('client_id'))->lockForUpdate()->first();
                if ($existing) {
                    abort_unless($existing->created_by === $request->user()->id && $existing->revision === 1
                        && $existing->draft == $request->validated('content')
                        && ($existing->published !== null) === ($request->input('action') === 'publish'), 409, 'Draf ini sudah dibuat sebelumnya. Buka kembali dari daftar program.');

                    return $existing;
                }
            }
            $before = $program?->draft ?? [];
            if ($program) {
                $program = AcademicProgram::whereKey($program->id)->lockForUpdate()->firstOrFail();
                abort_unless($program->revision === $request->integer('revision'), 409, 'Program telah diubah di tab atau perangkat lain. Muat ulang setelah menyalin isian Anda.');
                $before = $program->draft;
                $next = $request->validated('content');
                if (ContentComparison::same($program->draft, $next)
                    && ($request->input('action') !== 'publish' || ContentComparison::same($program->published, $next))) {
                    return $program;
                }
                $this->preserveNodes($program->draft, $next);
                $program->revision++;
            } else {
                $program = new AcademicProgram;
                $program->id = $request->input('client_id');
                $program->created_by = $request->user()->id;
                $program->revision = 1;
            }
            $program->draft = $request->validated('content');
            if ($request->input('action') === 'publish') {
                $program->published = $program->draft;
                $program->published_at = now();
            }
            $program->save();
            $revisionId = DB::table('program_revisions')->insertGetId([
                'program_id' => $program->id,
                'revision' => $program->revision,
                'action' => $request->input('action'),
                'content' => json_encode($program->draft, JSON_THROW_ON_ERROR),
                'actor_id' => $request->user()->id,
                'created_at' => now(),
            ]);

            ActivityLog::record($request->user()->id, $request->input('action') === 'publish' ? 'program.published' : 'program.draft.saved', 'Program disimpan: '.($program->draft['name'] ?: 'Tanpa nama'), $before, $program->draft, 'program', $program->id, 'program:'.$revisionId);
            ActivityLog::programLevels($request->user()->id, $before, $program->draft, $program->id);

            return $program;
        });

        return response()->json(['id' => $program->id, 'revision' => $program->revision, 'published_at' => $program->published_at?->toIso8601String()]);
    }

    private function preserveNodes(array $previous, array $next): void
    {
        $map = function (array $content) {
            $nodes = [];
            $walk = function (array $data, string $parent = 'program') use (&$walk, &$nodes): void {
                foreach ($data as $key => $items) {
                    if (! is_array($items)) {
                        continue;
                    }
                    foreach ($items as $item) {
                        if (is_array($item) && isset($item['id'])) {
                            $nodes[$item['id']] = $parent.':'.($key === 'archived_levels' ? 'levels' : $key);
                            $walk($item, $item['id']);
                        }
                    }
                }
            };
            $walk($content);

            return $nodes;
        };
        $before = $map($previous);
        $after = $map($next);
        foreach ($before as $id => $parent) {
            if (($after[$id] ?? null) !== $parent) {
                throw ValidationException::withMessages(['content' => 'Bagian yang sudah tersimpan tidak boleh dihapus atau dipindahkan diam-diam. Materi paket di luar rencana aktif tetap harus disertakan.']);
            }
        }
    }
}
