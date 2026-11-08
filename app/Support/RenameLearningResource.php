<?php

namespace App\Support;

use App\Models\LearningResource;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RenameLearningResource
{
    public static function run(string $id, string $title, User $actor): LearningResource
    {
        abort_unless($actor->can('learning.manage'), 403);
        $title = trim($title);
        Validator::make(['title' => $title], ['title' => ['required', 'string', 'max:255']])->validate();

        return DB::transaction(function () use ($id, $title, $actor) {
            $resource = LearningResource::whereKey($id)->lockForUpdate()->firstOrFail();
            $previous = $resource->versions()->where('revision', $resource->revision)->firstOrFail();
            if ($previous->title === $title) {
                return $resource;
            }
            $version = $previous->replicate(['id', 'created_at']);
            $version->title = $title;
            $version->revision = ++$resource->revision;
            $version->actor_id = $actor->id;
            $version->created_at = now();
            $resource->save();
            $version->save();
            ActivityLog::learning($version, $previous);

            return $resource;
        });
    }
}
