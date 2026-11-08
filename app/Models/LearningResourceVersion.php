<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LearningResourceVersion extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected function casts(): array
    {
        return ['content' => 'array', 'revision' => 'integer', 'created_at' => 'datetime'];
    }

    public function resource()
    {
        return $this->belongsTo(LearningResource::class, 'resource_id');
    }
}
