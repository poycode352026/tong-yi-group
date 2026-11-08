<?php

namespace App\Http\Requests\Admin;

use App\Models\LearningResource;
use App\Models\LearningResourceVersion;
use App\Support\LearningReferences;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveLearningResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('learning.manage') === true;
    }

    protected function prepareForValidation(): void
    {
        // Multipart FormData tidak membawa array kosong; pulihkan struktur kosongnya.
        if ($this->route('kind') === 'frameworks' && ! $this->has('levels')) {
            $this->merge(['levels' => []]);
        }
        if ($this->route('kind') === 'assessments') {
            $questions = $this->input('questions', []);
            if (is_array($questions)) {
                foreach ($questions as &$question) {
                    if (is_array($question)) {
                        $question['options'] ??= [];
                        if (isset($question['answer']) && ctype_digit((string) $question['answer'])) {
                            $question['answer'] = (int) $question['answer'];
                        }
                    }
                }
                $this->merge(['questions' => $questions]);
            }
        }
    }

    public function rules(): array
    {
        $ready = $this->input('status') === 'ready';
        $kind = $this->route('kind');

        return [
            ...LearningReferences::rules('references', $this->input('format') === 'module_level'),
            'target' => [$kind === 'assessments' && (! $this->route('resource') || isset($this->route('resource')->versions()->where('revision', $this->route('resource')->revision)->first()?->content['target'])) ? 'required' : 'nullable', 'array:program_id,program_revision,subprogram_id,level_id,package_id'],
            'target.program_id' => ['required_with:target', 'uuid'],
            'target.program_revision' => ['required_with:target', 'integer', 'min:1'],
            'target.subprogram_id' => ['required_with:target', 'uuid'],
            'target.level_id' => ['required_with:target', 'uuid'],
            'target.package_id' => ['nullable', 'uuid'],
            'group' => ['nullable', 'string', 'max:100'],
            'level_target' => [$this->input('format') === 'module_level' ? 'required' : 'nullable', 'array:program_id,program_revision,subprogram_id,level_id'],
            'level_target.program_id' => ['required_with:level_target', 'uuid'],
            'level_target.program_revision' => ['required_with:level_target', 'integer', 'min:1'],
            'level_target.subprogram_id' => ['required_with:level_target', 'uuid'],
            'level_target.level_id' => ['required_with:level_target', 'uuid'],
            'format' => [$kind === 'materials' ? 'nullable' : 'prohibited', Rule::in(['module', 'module_level', 'module_catalog'])],
            'framework_version_id' => [$this->input('format') === 'module' ? 'required' : 'nullable', 'uuid', Rule::exists('learning_resource_versions', 'id')->where('status', 'ready')->whereIn('resource_id', LearningResource::select('id')->where('kind', 'frameworks'))],
            'framework_level' => [$this->input('format') === 'module' ? 'required' : 'nullable', 'string', 'max:100'],
            'attachments' => [in_array($this->input('format'), ['module', 'module_level', 'module_catalog']) ? 'required' : 'sometimes', 'array', 'min:1', 'max:30'],
            'attachments.*' => ['array:id,title,purpose,description,source,url,file,file_id,legacy_version_id,file_name,file_url,preview_url,references,reference_labels'],
            'attachments.*.id' => ['required', 'uuid', 'distinct'],
            'attachments.*.title' => ['required', 'string', 'max:255'],
            'attachments.*.purpose' => ['nullable', 'string', 'max:255'],
            'attachments.*.description' => ['nullable', 'string', 'max:10000'],
            'attachments.*.source' => ['required', Rule::in(['pdf', 'link'])],
            'attachments.*.url' => ['nullable', 'url:https', 'max:2000'],
            'attachments.*.file' => ['nullable', 'file', 'mimes:pdf', 'extensions:pdf', 'max:2048'],
            'attachments.*.file_id' => ['nullable', 'uuid'],
            'attachments.*.legacy_version_id' => ['nullable', 'uuid'],
            ...LearningReferences::rules('attachments.*.references'),
            'revision' => ['required', 'integer', 'min:0'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'ready'])],
            'description' => ['nullable', 'string', 'max:10000'],
            'edition' => ['nullable', 'string', 'max:100'],
            'levels' => [$kind === 'frameworks' ? 'present' : 'prohibited', 'array', 'max:100'],
            'levels.*' => ['required', 'string', 'max:100', 'distinct'],
            'usage' => ['nullable', Rule::in(['reference', 'session'])],
            'source' => [$kind !== 'materials' ? 'prohibited' : (in_array($this->input('format'), ['module', 'module_level', 'module_catalog']) ? 'nullable' : 'required'), Rule::in(['pdf', 'link'])],
            'url' => ['nullable', $kind === 'materials' && $this->input('source') === 'link' && $ready ? 'required' : 'nullable', 'url:https', 'max:2000'],
            'author' => ['nullable', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'extensions:pdf', 'max:2048'],
            'scope' => [$kind === 'assessments' ? 'required' : 'prohibited', Rule::in(['package', 'level'])],
            'questions' => [$kind === 'assessments' ? 'present' : 'prohibited', 'array', 'max:200', ...($ready && $kind === 'assessments' ? ['min:1'] : [])],
            'questions.*' => ['array:id,type,prompt,options,answer,guide,media,image_file,audio_file,remove_image,remove_audio'],
            'questions.*.media' => ['nullable', 'array'],
            'questions.*.image_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048'],
            'questions.*.audio_file' => ['nullable', 'file', 'mimes:mp3,wav,m4a,ogg', 'extensions:mp3,wav,m4a,ogg', 'max:2048'],
            'questions.*.remove_image' => ['sometimes', 'boolean'],
            'questions.*.remove_audio' => ['sometimes', 'boolean'],
            'questions.*.id' => ['required', 'uuid', 'distinct'],
            'questions.*.type' => ['required', Rule::in(['choice', 'text'])],
            'questions.*.prompt' => [$ready ? 'required' : 'nullable', 'string', 'max:10000'],
            'questions.*.options' => ['present', 'array', 'max:6'],
            'questions.*.options.*' => ['nullable', 'string', 'max:2000'],
            'questions.*.answer' => ['nullable', 'integer', 'min:0', 'max:5'],
            'questions.*.guide' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $references = $this->input('references');
            if ($this->input('format') !== 'module_catalog' || ! empty($references['frameworks']) || ! empty($references['internal'])) {
                $previous = $this->route('resource')?->versions()->where('revision', $this->route('resource')->revision)->first();
                LearningReferences::validate($references, 'references', $validator, $previous ? LearningReferences::forVersion($previous) : null);
            }
            if ($this->input('format') === 'module_catalog') {
                foreach ($references['frameworks'] ?? [] as $i => $mapping) {
                    $source = LearningResourceVersion::find($mapping['version_id']);
                    if (! empty($source?->content['levels']) && empty($mapping['levels'])) {
                        $validator->errors()->add("references.frameworks.$i.levels", 'Pilih minimal satu tingkatan untuk acuan ini.');
                    }
                }
            }
            foreach ($this->input('attachments', []) as $i => $row) {
                $oldVersion = $this->route('resource')?->versions()->where('revision', $this->route('resource')->revision)->first();
                $oldRow = collect($oldVersion?->content['attachments'] ?? [])->firstWhere('id', $row['id']);
                LearningReferences::validate($row['references'] ?? null, "attachments.$i.references", $validator, $oldRow['references'] ?? null);
                if ($this->input('format') === 'module_catalog' && isset($row['references'])) {
                    $unchanged = isset($oldRow['references']) && LearningReferences::normalize($row['references']) === LearningReferences::normalize($oldRow['references']);
                    if (! $unchanged) {
                        foreach ($row['references']['frameworks'] ?? [] as $mapping) {
                            $parent = collect($references['frameworks'] ?? [])->firstWhere('version_id', $mapping['version_id']);
                            if (! $parent || array_diff($mapping['levels'], $parent['levels']) || (! empty($parent['levels']) && empty($mapping['levels']))) {
                                $validator->errors()->add("attachments.$i.references", 'Pilih cakupan bahan dari tingkatan yang dicentang pada modul.');
                            }
                        }
                    }
                }

            }
            if ($this->input('status') !== 'ready') {
                return;
            }
            if (in_array($this->input('format'), ['module', 'module_level', 'module_catalog'])) {
                if ($this->route('kind') !== 'materials') {
                    $validator->errors()->add('format', 'Format modul hanya digunakan pada Pustaka Guru.');
                }
                if ($this->input('format') === 'module') {
                    $framework = LearningResourceVersion::find($this->input('framework_version_id'));
                    if (! in_array($this->input('framework_level'), $framework?->content['levels'] ?? [], true)) {
                        $validator->errors()->add('framework_level', 'Pilih tingkatan dari acuan yang tersedia.');
                    }
                }
                $size = 0;
                foreach ($this->input('attachments', []) as $i => $item) {
                    if (($item['source'] ?? '') === 'link' && empty($item['url'])) {
                        $validator->errors()->add("attachments.$i.url", 'Isi tautan HTTPS.');
                    }
                    $size += $this->file("attachments.$i.file")?->getSize() ?? 0;
                }
                if ($size > 6 * 1024 * 1024) {
                    $validator->errors()->add('attachments', 'Total unggahan maksimal 6 MB sekali simpan. Simpan sebagian berkas terlebih dahulu, lalu tambahkan berkas berikutnya.');
                }
            }
            $mediaSize = 0;
            foreach ($this->file('questions', []) as $files) {
                foreach (['image_file', 'audio_file'] as $key) {
                    $mediaSize += ($files[$key] ?? null)?->getSize() ?? 0;
                }
            }
            if ($mediaSize > 6 * 1024 * 1024) {
                $validator->errors()->add('questions', 'Total unggahan media maksimal 6 MB sekali simpan.');
            }
            foreach ($this->input('questions', []) as $i => $q) {
                if ($q['type'] === 'choice') {
                    $options = $q['options'];
                    if (count($options) < 2 || count(array_filter($options, fn ($v) => is_string($v) && trim($v) !== '')) !== count($options) || count(array_unique($options)) !== count($options)) {
                        $validator->errors()->add("questions.$i.options", 'Isi 2–6 pilihan jawaban yang berbeda.');
                    }
                    if (! isset($q['answer']) || ! array_key_exists($q['answer'], $options)) {
                        $validator->errors()->add("questions.$i.answer", 'Pilih kunci jawaban yang tersedia.');
                    }
                } elseif (empty(trim($q['guide'] ?? ''))) {
                    $validator->errors()->add("questions.$i.guide", 'Lengkapi acuan pemeriksaan isian untuk petugas pemeriksa.');
                }
            }
        }];
    }

    public function messages(): array
    {
        return ['file.uploaded' => 'Berkas tidak dapat diunggah. Pastikan PDF tidak melebihi 2 MB.', 'required' => 'Isian ini wajib diisi.', 'max' => 'Melebihi batas teknis :max.', 'min' => 'Tambahkan minimal :min soal.', 'mimes' => 'Unggah berkas PDF.', 'extensions' => 'Ekstensi berkas harus PDF.', 'url' => 'Gunakan tautan HTTPS yang valid.', 'in' => 'Pilihan tidak valid.', 'distinct' => 'Isian tidak boleh berulang.', 'prohibited' => 'Isian ini tidak digunakan pada jenis ini.'];
    }
}
