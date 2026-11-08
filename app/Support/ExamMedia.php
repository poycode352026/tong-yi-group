<?php

namespace App\Support;

use App\Models\LearningResourceVersion;
use Illuminate\Http\UploadedFile;

class ExamMedia
{
    public static function save(array $questions, ?LearningResourceVersion $previous, array &$uploaded): array
    {
        $old = collect($previous?->content['questions'] ?? [])->keyBy('id');
        foreach ($questions as &$question) {
            $media = $old->get($question['id'])['media'] ?? [];
            foreach (['image', 'audio'] as $kind) {
                if (! empty($question['remove_'.$kind])) {
                    unset($media[$kind]);
                }
                $file = $question[$kind.'_file'] ?? null;
                if ($file instanceof UploadedFile && ! ModuleFiles::identicalUpload($file, $media[$kind]['file_path'] ?? null, $media[$kind]['name'] ?? null)) {
                    $path = $file->store('exam-media', 'learning');
                    abort_unless($path, 500, 'Media tidak dapat disimpan.');
                    $uploaded[] = $path;
                    $media[$kind] = ['file_path' => $path, 'name' => mb_substr(basename($file->getClientOriginalName()), 0, 200), 'mime' => $file->getMimeType()];
                }
                unset($question[$kind.'_file'], $question['remove_'.$kind]);
            }
            unset($question['media']);
            if ($media) {
                $question['media'] = $media;
            }
        }

        return $questions;
    }

    public static function present(LearningResourceVersion $version): array
    {
        return array_map(function ($question) use ($version) {
            foreach ($question['media'] ?? [] as $kind => $media) {
                $question['media'][$kind] = ['name' => $media['name'], 'url' => '/admin/learning/versions/'.$version->id.'/questions/'.$question['id'].'/'.$kind];
            }

            return $question;
        }, $version->content['questions'] ?? []);
    }
}
