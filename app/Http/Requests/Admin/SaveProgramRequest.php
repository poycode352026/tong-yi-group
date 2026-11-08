<?php

namespace App\Http\Requests\Admin;

use App\Models\LearningResource;
use App\Models\LearningResourceVersion;
use App\Support\ExamPlacement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('programs.manage') === true
            && ($this->input('action') !== 'publish' || $this->user()->can('programs.publish'));
    }

    public function rules(): array
    {
        $required = $this->input('action') === 'publish' ? 'required' : 'nullable';

        $rules = [
            'client_id' => [$this->routeIs('admin.programs.store') ? 'required' : 'nullable', 'uuid'],
            'action' => ['required', Rule::in(['draft', 'publish'])],
            'revision' => ['required', 'integer', 'min:0'],
            'content' => ['required', 'array:name,category,description,subprograms'],
            'content.name' => ['present', $required, 'string', 'max:255'],
            'content.category' => ['present', $required, 'string', 'max:100'],
            'content.description' => ['nullable', 'string', 'max:10000'],
            'content.subprograms' => ['present', 'array'],
            'content.subprograms.*' => ['array:id,name,audience,variant,levels,archived_levels'],
            'content.subprograms.*.id' => ['required', 'uuid'],
            'content.subprograms.*.name' => [$required, 'string', 'max:255'],
            'content.subprograms.*.audience' => ['nullable', 'string', 'max:100'],
            'content.subprograms.*.variant' => ['nullable', 'string', 'max:100'],
            'content.subprograms.*.levels' => ['present', 'array'],
            'content.subprograms.*.levels.*' => ['array:id,name,package_count,services,packages,frameworks,material_versions,assessment_versions,module_title,module_description'],
            'content.subprograms.*.levels.*.id' => ['required', 'uuid'],
            'content.subprograms.*.levels.*.module_title' => ['nullable', 'string', 'max:255'],
            'content.subprograms.*.levels.*.module_description' => ['nullable', 'string', 'max:10000'],
            'content.subprograms.*.levels.*.packages.*.sessions.*.module_section' => ['nullable', 'string', 'max:255'],
            'content.subprograms.*.levels.*.name' => [$required, 'string', 'max:255'],
            'content.subprograms.*.levels.*.package_count' => ['required', 'integer', 'min:1', 'max:100'],
            'content.subprograms.*.levels.*.services' => ['present', 'array'],
            'content.subprograms.*.levels.*.services.*' => ['array:id,name,delivery,price,minimum,maximum'],
            'content.subprograms.*.levels.*.services.*.id' => ['required', 'uuid'],
            'content.subprograms.*.levels.*.services.*.name' => [$required, 'string', 'max:100'],
            'content.subprograms.*.levels.*.services.*.delivery' => [$required, Rule::in(['online', 'offline'])],
            'content.subprograms.*.levels.*.services.*.price' => [$required, 'numeric', 'min:0', 'decimal:0,2'],
            'content.subprograms.*.levels.*.services.*.minimum' => [$required, 'integer', 'min:1'],
            'content.subprograms.*.levels.*.services.*.maximum' => [$required, 'integer', 'min:1'],
            'content.subprograms.*.levels.*.packages' => ['present', 'array', 'max:100'],
            'content.subprograms.*.levels.*.packages.*' => ['array:id,theme,sessions,material_versions,assessment_versions'],
            'content.subprograms.*.levels.*.packages.*.id' => ['required', 'uuid'],
            'content.subprograms.*.levels.*.packages.*.theme' => ['nullable', 'string', 'max:255'],
            'content.subprograms.*.levels.*.packages.*.sessions' => ['required', 'array', 'size:8'],
            'content.subprograms.*.levels.*.packages.*.sessions.*' => ['array:id,title,objectives,material,exercise,reference,material_versions,module_section'],
            'content.subprograms.*.levels.*.packages.*.sessions.*.id' => ['required', 'uuid'],
            'content.subprograms.*.levels.*.packages.*.sessions.*.title' => ['nullable', 'string', 'max:255'],
            'content.subprograms.*.levels.*.packages.*.sessions.*.objectives' => ['nullable', 'string', 'max:10000'],
            'content.subprograms.*.levels.*.packages.*.sessions.*.material' => ['nullable', 'string', 'max:20000'],
            'content.subprograms.*.levels.*.packages.*.sessions.*.exercise' => ['nullable', 'string', 'max:10000'],
            'content.subprograms.*.levels.*.packages.*.sessions.*.reference' => ['nullable', 'string', 'max:10000'],
        ];
        $levelPath = 'content.subprograms.*.levels.*';
        foreach ([$levelPath, "$levelPath.packages.*", "$levelPath.packages.*.sessions.*"] as $path) {
            $rules["$path.material_versions"] = ['sometimes', 'array', 'max:100'];
            $rules["$path.material_versions.*"] = ['uuid', Rule::exists('learning_resource_versions', 'id')->where('status', 'ready')->whereIn('resource_id', LearningResource::select('id')->where('kind', 'materials'))];
            if (! str_ends_with($path, 'sessions.*')) {
                $rules["$path.assessment_versions"] = ['sometimes', 'array', 'max:100'];
                $rules["$path.assessment_versions.*"] = ['uuid', Rule::exists('learning_resource_versions', 'id')->where('status', 'ready')->whereIn('resource_id', LearningResource::select('id')->where('kind', 'assessments'))];
            }
        }
        $rules["$levelPath.frameworks"] = ['sometimes', 'array', 'max:100'];
        $rules["$levelPath.frameworks.*"] = ['array:version_id,levels'];
        $rules["$levelPath.frameworks.*.version_id"] = ['required', 'uuid', Rule::exists('learning_resource_versions', 'id')->where('status', 'ready')->whereIn('resource_id', LearningResource::select('id')->where('kind', 'frameworks'))];
        $rules["$levelPath.frameworks.*.levels"] = ['present', 'array', 'max:100'];
        $rules["$levelPath.frameworks.*.levels.*"] = ['required', 'string', 'max:100'];
        foreach ($rules as $path => $rule) {
            if ($path === 'content.subprograms.*.levels' || str_starts_with($path, 'content.subprograms.*.levels.')) {
                $archivePath = str_replace('subprograms.*.levels', 'subprograms.*.archived_levels', $path);
                if ($path === 'content.subprograms.*.levels') {
                    $rule = ['sometimes', 'array'];
                } elseif (in_array(substr($path, strrpos($path, '.') + 1), ['name', 'delivery', 'price', 'minimum', 'maximum'])) {
                    $rule = array_map(fn ($item) => $item === 'required' ? 'nullable' : $item, $rule);
                }
                $rules[$archivePath] = $rule;
            }
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            if (! $this->user()->can('manage-access') && $this->route('program')) {
                $archived = [];
                foreach ($this->route('program')->draft['subprograms'] as $sub) {
                    foreach ($sub['archived_levels'] ?? [] as $level) {
                        $archived[$level['id']] = $level;
                    }
                }
                foreach ($this->input('content.subprograms', []) as $sub) {
                    foreach ($sub['archived_levels'] ?? [] as $level) {
                        if (isset($archived[$level['id']]) && $level != $archived[$level['id']]) {
                            $validator->errors()->add('content', 'Perubahan arsip level hanya dapat dilakukan Superadmin.');
                        }
                    }
                    foreach ($sub['levels'] as $level) {
                        if (isset($archived[$level['id']])) {
                            $validator->errors()->add('content', 'Pemulihan level hanya dapat dilakukan Superadmin melalui Sistem → Arsip Level.');
                        }
                    }
                }
            }
            $this->validateMaterialPlacement($validator);
            $seen = [];
            $visit = function (array $node) use (&$visit, &$seen, $validator): void {
                if (isset($node['id'])) {
                    $id = strtolower($node['id']);
                    if (isset($seen[$id])) {
                        $validator->errors()->add('content', 'Identitas bagian program tidak boleh digunakan dua kali.');
                    }
                    $seen[$id] = true;
                }
                foreach ($node as $child) {
                    if (is_array($child)) {
                        $visit($child);
                    }
                }
            };
            $visit($this->input('content'));
            foreach ($this->input('content.subprograms', []) as $s => $subprogram) {
                foreach (['levels', 'archived_levels'] as $collection) {
                    foreach (($subprogram[$collection] ?? []) as $l => $level) {
                        $prefix = "content.subprograms.$s.$collection.$l";
                        if (count($level['packages']) < $level['package_count']) {
                            $validator->errors()->add("$prefix.package_count", 'Rencana paket belum lengkap. Setiap paket harus berisi delapan sesi.');
                        }
                        foreach ($level['frameworks'] ?? [] as $f => $mapping) {
                            $version = LearningResourceVersion::find($mapping['version_id']);
                            if (array_diff($mapping['levels'], $version->content['levels'] ?? [])) {
                                $validator->errors()->add("$prefix.frameworks.$f.levels", 'Tingkatan harus berasal dari versi acuan yang dipilih.');
                            }
                        }
                        foreach ([$level, ...$level['packages']] as $p => $node) {
                            foreach ($node['assessment_versions'] ?? [] as $a => $id) {
                                $version = LearningResourceVersion::find($id);
                                $target = $version->content['target'] ?? null;
                                $old = $this->route('program')?->draft;
                                $oldSub = collect($old['subprograms'] ?? [])->firstWhere('id', $subprogram['id']);
                                $oldLevel = collect($oldSub[$collection] ?? [])->firstWhere('id', $level['id']);
                                $oldNode = $p === 0 ? $oldLevel : collect($oldLevel['packages'] ?? [])->firstWhere('id', $node['id']);
                                if ($target && ! in_array($id, $oldNode['assessment_versions'] ?? [], true)
                                    && ! ExamPlacement::matches($target, $this->route('program')?->id ?? $this->input('client_id'), $subprogram['id'], $level['id'], $p === 0 ? null : $node['id'])) {
                                    $validator->errors()->add($p === 0 ? "$prefix.assessment_versions.$a" : "$prefix.packages.".($p - 1).".assessment_versions.$a", 'Ujian ini ditujukan untuk program, level, atau paket lain.');
                                }
                                if (($version->content['scope'] ?? '') !== ($p === 0 ? 'level' : 'package')) {
                                    $validator->errors()->add($p === 0 ? "$prefix.assessment_versions.$a" : "$prefix.packages.".($p - 1).".assessment_versions.$a", 'Cakupan ujian tidak sesuai penempatan paket/akhir level.');
                                }
                            }
                        }
                        foreach ($level['services'] as $v => $service) {
                            if (isset($service['minimum'], $service['maximum']) && $service['maximum'] < $service['minimum']) {
                                $validator->errors()->add("$prefix.services.$v.maximum", 'Kapasitas maksimum tidak boleh lebih kecil dari minimum.');
                            }
                        }
                    }
                }
            }
        }];
    }

    private function validateMaterialPlacement(Validator $validator): void
    {
        $ids = [];
        $collect = function (array $node) use (&$collect, &$ids): void {
            foreach ($node as $key => $value) {
                if ($key === 'material_versions') {
                    $ids = [...$ids, ...$value];
                } elseif (is_array($value)) {
                    $collect($value);
                }
            }
        };
        $collect($this->input('content'));
        $collect($this->route('program')?->draft ?? []);
        $versions = LearningResourceVersion::whereIn('id', array_unique($ids))->get()->keyBy('id');
        $placements = function (array $content) use ($versions): array {
            $result = [];
            foreach ($content['subprograms'] ?? [] as $sub) {
                foreach ($sub['levels'] ?? [] as $level) {
                    foreach ($level['packages'] ?? [] as $pack) {
                        foreach ($pack['sessions'] ?? [] as $session) {
                            foreach ($session['material_versions'] ?? [] as $id) {
                                $resourceId = $versions->get($id)?->resource_id ?? $id;
                                $result[$resourceId][$session['id']] = true;
                            }
                        }
                    }
                }
            }

            return $result;
        };
        $previousSessions = [];
        $previousLevelModules = [];
        foreach (($this->route('program')?->draft['subprograms'] ?? []) as $sub) {
            foreach ([...($sub['levels'] ?? []), ...($sub['archived_levels'] ?? [])] as $level) {
                $previousLevelModules[$sub['id']][$level['id']] = $level['material_versions'] ?? [];
                foreach ($level['packages'] ?? [] as $pack) {
                    foreach ($pack['sessions'] ?? [] as $session) {
                        $previousSessions[$session['id']] = $session['material_versions'] ?? [];
                    }
                }
            }
        }
        $before = $placements($this->route('program')?->draft ?? []);
        $after = $placements($this->input('content'));
        foreach ($this->input('content.subprograms', []) as $s => $sub) {
            foreach ($sub['levels'] as $l => $level) {
                $prefix = "content.subprograms.$s.levels.$l";
                foreach ($level['material_versions'] ?? [] as $m => $id) {
                    if (($versions->get($id)?->content['usage'] ?? null) === 'session') {
                        $validator->errors()->add("$prefix.material_versions.$m", 'Pilih bahan referensi dari menu Modul untuk level ini.');
                    }
                }
                foreach ($level['packages'] as $p => $pack) {
                    foreach ($pack['material_versions'] ?? [] as $m => $id) {
                        if (($versions->get($id)?->content['usage'] ?? null) === 'session') {
                            $validator->errors()->add("$prefix.packages.$p.material_versions.$m", 'Materi sesi ditempatkan pada sesi, bukan referensi paket.');
                        }
                    }
                    foreach ($pack['sessions'] as $t => $session) {
                        $currentIds = $session['material_versions'] ?? [];
                        $previousIds = $previousSessions[$session['id']] ?? [];
                        sort($currentIds);
                        sort($previousIds);
                        if (count($currentIds) > 1 && $currentIds !== $previousIds) {
                            $validator->errors()->add("$prefix.packages.$p.sessions.$t.material_versions", 'Setiap sesi menggunakan satu PDF. Lepas materi sebelumnya sebelum mengganti.');
                        }
                        foreach ($session['material_versions'] ?? [] as $m => $id) {
                            $version = $versions->get($id);
                            $path = "$prefix.packages.$p.sessions.$t.material_versions.$m";
                            if (($version?->content['usage'] ?? null) === 'reference') {
                                $validator->errors()->add($path, 'Modul referensi guru dipilih pada level atau paket. Unggah materi khusus sesi ini.');
                            }
                            $resourceId = $version?->resource_id ?? $id;
                            $now = array_keys($after[$resourceId] ?? []);
                            $old = array_keys($before[$resourceId] ?? []);
                            sort($now);
                            sort($old);
                            if (count($now) > 1 && $now !== $old) {
                                $validator->errors()->add($path, 'Materi ini sudah digunakan pada sesi lain. Gunakan materi berbeda untuk setiap sesi.');
                            }
                        }
                    }
                }
            }
        }
    }

    public function messages(): array
    {
        return [
            'required' => 'Isian ini wajib dilengkapi sebelum disimpan.',
            'present' => 'Bagian data ini belum tersedia.',
            'string' => 'Isian harus berupa teks.',
            'array' => 'Susunan data tidak valid.',
            'uuid' => 'Identitas bagian data tidak valid.',
            'numeric' => 'Isian harus berupa angka.',
            'integer' => 'Isian harus berupa bilangan bulat.',
            'min' => 'Nilai isian kurang dari batas yang diizinkan (:min).',
            'max' => 'Isian melebihi batas teknis (:max).',
            'size' => 'Setiap paket harus memiliki delapan sesi.',
            'in' => 'Pilihan isian tidak valid.',
            'exists' => 'Pilih versi siap dipakai dari jenis sumber yang sesuai.',
            'distinct' => 'Pilihan tidak boleh berulang.',
            'decimal' => 'Harga menggunakan maksimal dua angka desimal.',
        ];
    }
}
