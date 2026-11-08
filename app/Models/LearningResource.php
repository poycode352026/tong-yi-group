<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LearningResource extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return ['revision' => 'integer'];
    }

    public function versions()
    {
        return $this->hasMany(LearningResourceVersion::class, 'resource_id');
    }
}
