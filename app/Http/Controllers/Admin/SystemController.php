<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use App\Support\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SystemController extends Controller
{
    public function activity(Request $request)
    {
        $search = mb_substr(trim((string) $request->query('search')), 0, 100);
        $action = (string) $request->query('action');
        $query = DB::table('admin_audit_logs as a')->leftJoin('users as u', 'u.id', '=', 'a.actor_id')->leftJoin('users as t', 't.id', '=', 'a.target_user_id');
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('u.name', 'ilike', '%'.$search.'%')->orWhere('a.summary', 'ilike', '%'.$search.'%')->orWhere('a.action', 'ilike', '%'.$search.'%'));
        }
        if ($action !== '') {
            $query->where('a.action', $action);
        }
        $from = $request->query('from');
        $to = $request->query('to');
        $request->validate(['from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])]]);
        if ($from) {
            $query->where('a.created_at', '>=', Carbon::parse($from, 'Asia/Pontianak')->startOfDay()->utc());
        }
        if ($to) {
            $query->where('a.created_at', '<=', Carbon::parse($to, 'Asia/Pontianak')->endOfDay()->utc());
        }

        return Inertia::render('Admin/System/Activity', [
            'filters' => compact('search', 'action', 'from', 'to'),
            'actions' => DB::table('admin_audit_logs')->distinct()->orderBy('action')->pluck('action'),
            'logs' => $query->select('a.id', 'a.action', 'a.summary', 'a.outcome', 'a.created_at', 'u.name as actor', 't.name as target')->orderByDesc('a.created_at')->orderByDesc('a.id')->paginate(25)->withQueryString(),
        ]);
    }

    public function detail(int $log)
    {
        $entry = DB::table('admin_audit_logs as a')->leftJoin('users as u', 'u.id', '=', 'a.actor_id')->leftJoin('users as t', 't.id', '=', 'a.target_user_id')->where('a.id', $log)->select('a.*', 'u.name as actor', 't.name as target')->first();
        abort_unless($entry, 404);
        $before = ActivityLog::sanitize(json_decode($entry->before, true) ?? []);
        $after = ActivityLog::sanitize(json_decode($entry->after, true) ?? []);
        $changes = [];
        $walk = function ($old, $new, string $path = '') use (&$walk, &$changes): void {
            if ($old === null && is_array($new)) {
                $old = [];
            }
            if ($new === null && is_array($old)) {
                $new = [];
            }
            if ($old === $new) {
                return;
            }
            if (is_array($old) && is_array($new)) {
                foreach (array_unique([...array_keys($old), ...array_keys($new)]) as $key) {
                    if (in_array($key, ['password', 'remember_token', 'token', 'file_path', 'answer', 'guide'])) {
                        continue;
                    }
                    $walk($old[$key] ?? null, $new[$key] ?? null, ltrim($path.'.'.$key, '.'));
                }
            } else {
                $changes[] = ['field' => $path ?: 'Data', 'before' => $old, 'after' => $new];
            }
        };
        $walk($before, $after);

        return Inertia::render('Admin/System/ActivityDetail', ['entry' => ['id' => $entry->id, 'action' => $entry->action, 'summary' => $entry->summary, 'actor' => $entry->actor, 'target' => $entry->target, 'created_at' => $entry->created_at, 'outcome' => $entry->outcome], 'changes' => $changes,
            'versionUrl' => isset($after['version_id']) ? '/admin/learning/versions/'.$after['version_id'] : null]);
    }

    public function archives(Request $request)
    {
        $search = mb_substr(trim((string) $request->query('search')), 0, 100);
        $query = DB::table('academic_programs as p')
            ->crossJoin(DB::raw("LATERAL jsonb_array_elements(p.draft->'subprograms') as s(value)"))
            ->crossJoin(DB::raw("LATERAL jsonb_array_elements(COALESCE(s.value->'archived_levels', '[]'::jsonb)) as l(value)"))
            ->selectRaw("p.id as program_id, p.revision, p.draft->>'name' as program_name, s.value->>'id' as subprogram_id, s.value->>'name' as subprogram_name, l.value->>'id' as level_id, l.value->>'name' as level_name, jsonb_array_length(l.value->'packages') as packages");
        if ($search !== '') {
            $query->whereRaw("(p.draft->>'name' ILIKE ? OR l.value->>'name' ILIKE ?)", ['%'.$search.'%', '%'.$search.'%']);
        }

        return Inertia::render('Admin/System/Archives', ['archives' => $query->orderBy('p.id')->orderByRaw("l.value->>'id'")->paginate(20)->withQueryString(), 'search' => $search]);
    }

    public function restore(Request $request, AcademicProgram $program)
    {
        $data = $request->validate(['revision' => ['required', 'integer'], 'subprogram_id' => ['required', 'uuid'], 'level_id' => ['required', 'uuid']]);
        DB::transaction(function () use ($request, $data, $program) {
            $program = AcademicProgram::whereKey($program->id)->lockForUpdate()->firstOrFail();
            if ($program->revision !== (int) $data['revision']) {
                throw ValidationException::withMessages(['revision' => 'Program telah berubah. Muat ulang arsip sebelum mencoba kembali.']);
            }
            $before = $program->draft;
            $content = $before;
            $found = false;
            foreach ($content['subprograms'] as &$sub) {
                if ($sub['id'] !== $data['subprogram_id']) {
                    continue;
                }
                foreach ($sub['archived_levels'] ?? [] as $index => $level) {
                    if ($level['id'] !== $data['level_id']) {
                        continue;
                    }
                    $sub['levels'][] = $level;
                    array_splice($sub['archived_levels'], $index, 1);
                    $found = true;
                    break;
                }
            }
            unset($sub);
            abort_unless($found, 404);
            $program->draft = $content;
            $program->revision++;
            $program->save();
            $id = DB::table('program_revisions')->insertGetId(['program_id' => $program->id, 'revision' => $program->revision, 'action' => 'draft', 'content' => json_encode($content, JSON_THROW_ON_ERROR), 'actor_id' => $request->user()->id, 'created_at' => now()]);
            ActivityLog::record($request->user()->id, 'level.restored', 'Level dikembalikan ke draf program', $before, $content, 'program', $program->id, 'program:'.$id);
        });

        return back()->with('success', 'Level dikembalikan ke draf. Terbitkan program untuk menerapkannya pada versi terbit.');
    }
}
