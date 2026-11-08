<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AcademicProgram extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return ['draft' => 'array', 'published' => 'array', 'published_at' => 'datetime'];
    }
}
