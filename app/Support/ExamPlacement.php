<?php

namespace App\Support;

use App\Models\AcademicProgram;
use Illuminate\Validation\ValidationException;

class ExamPlacement
{
    public static function options(): array
    {
        return AcademicProgram::orderBy('created_at')->get()->map(fn ($program) => [
            'id' => $program->id, 'revision' => $program->revision, 'name' => $program->draft['name'] ?: 'Program belum bernama',
            'subprograms' => array_map(fn ($sub) => ['id' => $sub['id'], 'name' => $sub['name'], 'levels' => array_map(fn ($level) => [
                'id' => $level['id'], 'name' => $level['name'],
                'packages' => array_map(fn ($package, $i) => ['id' => $package['id'], 'name' => 'Paket '.($i + 1), 'sessions' => count($package['sessions'] ?? [])], array_slice($level['packages'], 0, $level['package_count']), array_keys(array_slice($level['packages'], 0, $level['package_count']))),
            ], $sub['levels'])], $program->draft['subprograms']),
        ])->all();
    }

    public static function resolve(array $target, string $scope): array
    {
        $program = AcademicProgram::whereKey($target['program_id'])->lockForUpdate()->first();
        if (! $program || $program->revision !== (int) $target['program_revision']) {
            throw ValidationException::withMessages(['target.program_id' => 'Program telah berubah atau tidak tersedia. Muat ulang pilihan sebelum menyimpan.']);
        }
        $sub = collect($program->draft['subprograms'])->firstWhere('id', $target['subprogram_id']);
        $level = collect($sub['levels'] ?? [])->firstWhere('id', $target['level_id']);
        if (! $level) {
            throw ValidationException::withMessages(['target.level_id' => 'Pilih level aktif dari subprogram yang dipilih.']);
        }
        $packages = array_slice($level['packages'], 0, $level['package_count']);
        $start = 1;
        $end = 0;
        $packageName = null;
        if ($scope === 'package') {
            $found = false;
            foreach ($packages as $i => $package) {
                $end += count($package['sessions'] ?? []);
                if ($package['id'] === ($target['package_id'] ?? null)) {
                    $packageName = 'Paket '.($i + 1);
                    $found = true;
                    break;
                }
                $start = $end + 1;
            }
            if (! $found) {
                throw ValidationException::withMessages(['target.package_id' => 'Pilih paket aktif dari level yang dipilih.']);
            }
        } else {
            $end = array_sum(array_map(fn ($p) => count($p['sessions'] ?? []), $packages));
        }
        if ($end < $start) {
            throw ValidationException::withMessages(['target.level_id' => 'Susun paket dan sesi level terlebih dahulu.']);
        }

        return ['program_id' => $program->id, 'subprogram_id' => $sub['id'], 'level_id' => $level['id'], 'package_id' => $scope === 'package' ? $target['package_id'] : null,
            'program_name' => $program->draft['name'], 'subprogram_name' => $sub['name'], 'level_name' => $level['name'], 'package_name' => $packageName, 'session_start' => $start, 'session_end' => $end];
    }

    public static function matches(array $target, string $program, string $sub, string $level, ?string $package): bool
    {
        return $target['program_id'] === $program && $target['subprogram_id'] === $sub && $target['level_id'] === $level && ($target['package_id'] ?? null) === $package;
    }
}
