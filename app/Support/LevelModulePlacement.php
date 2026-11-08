<?php

namespace App\Support;

use App\Models\AcademicProgram;
use Illuminate\Validation\ValidationException;

class LevelModulePlacement
{
    public static function resolve(array $target): array
    {
        $program = AcademicProgram::whereKey($target['program_id'])->lockForUpdate()->first();
        if (! $program || $program->revision !== (int) $target['program_revision']) {
            throw ValidationException::withMessages(['level_target.program_id' => 'Program telah berubah. Muat ulang pilihan sebelum menyimpan.']);
        }
        $sub = collect($program->draft['subprograms'])->firstWhere('id', $target['subprogram_id']);
        $level = collect($sub['levels'] ?? [])->firstWhere('id', $target['level_id']);
        if (! $level) {
            throw ValidationException::withMessages(['level_target.level_id' => 'Pilih level aktif dari subprogram yang dipilih.']);
        }

        return ['program_id' => $program->id, 'subprogram_id' => $sub['id'], 'level_id' => $level['id'],
            'program_name' => $program->draft['name'], 'subprogram_name' => $sub['name'], 'level_name' => $level['name']];
    }
}
