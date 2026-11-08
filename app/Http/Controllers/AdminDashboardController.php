<?php

namespace App\Http\Controllers;

use App\Models\AcademicProgram;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        if (! $request->user()->can('programs.view')) {
            return Inertia::render('Admin/Dashboard', ['admin' => $request->user()->only('name', 'email'), 'statistics' => null, 'recentPrograms' => []]);
        }
        $counts = AcademicProgram::query()->selectRaw('COUNT(*) AS total, COUNT(*) FILTER (WHERE published IS NULL) AS drafts, COUNT(*) FILTER (WHERE published IS NOT NULL) AS published')->first();

        return Inertia::render('Admin/Dashboard', [
            'admin' => $request->user()->only('name', 'email'),
            'statistics' => [
                'total' => (int) $counts->total,
                'drafts' => (int) $counts->drafts,
                'published' => (int) $counts->published,
            ],
            'recentPrograms' => AcademicProgram::orderByDesc('updated_at')->limit(5)->get()->map(fn ($program) => [
                'id' => $program->id,
                'name' => $program->draft['name'] ?: 'Program tanpa judul',
                'category' => $program->draft['category'],
                'published' => $program->published !== null,
                'has_changes' => $program->published !== null && $program->draft !== $program->published,
                'updated_at' => $program->updated_at->toIso8601String(),
            ]),
        ]);
    }
}
