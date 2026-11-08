<script setup>
import { automaticExams } from '../utils/automaticExams';
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { levelModules, selectedLevelModules } from '../utils/levelModules';
import { latestResourceChoices } from '../utils/resourceChoices';
const props = defineProps({ node: Object, catalog: Array, scope: String, name: String, placement: Object, sections: { type: Array, default: () => ['frameworks', 'materials', 'assessments'] }, usedSessionResources: { type: Array, default: () => [] } });
const addModuleUrl = computed(() => `/admin/learning/materials/create?${new URLSearchParams({ program: props.placement?.program_id ?? '', subprogram: props.placement?.subprogram_id ?? '', level: props.placement?.level_id ?? '' })}`);
const abilities = usePage().props.auth?.abilities ?? {};
const pickFramework = ref('');
const matchingModules = computed(() => levelModules(props.catalog ?? [], props.placement, props.node.material_versions ?? []));
const selectedModules = computed(() => selectedLevelModules(props.catalog ?? [], props.node.material_versions ?? []));
const moduleChoices = computed(() => matchingModules.value.filter((item) => !(props.node.material_versions ?? []).includes(item.id)));
function chooseModule() { add('material_versions', pickMaterial.value); pickMaterial.value = ''; }
function detachModule(id) { const index = props.node.material_versions?.indexOf(id); if (index >= 0) remove('material_versions', index); }
const pickMaterial = ref('');
const exams = computed(() => automaticExams(props.catalog ?? [], props.placement, props.scope, props.node.id));
const legacyExams = computed(() => (props.node.assessment_versions ?? []).map((id) => props.catalog?.find((item) => item.id === id)).filter((item) => item && !item.target));
const selectedIds = () => [...(props.node.frameworks ?? []).map((item) => item.version_id), ...(props.node.material_versions ?? []), ...(props.node.assessment_versions ?? [])];
const kindItems = (kind) => latestResourceChoices(props.catalog ?? [], selectedIds()).filter((item) => {
    if (item.kind !== kind || (kind === 'assessments' && item.scope !== props.scope)) return false;
    if (kind === 'assessments' && item.target) {
        const target = item.target, place = props.placement;
        if (!place || target.program_id !== place.program_id || target.subprogram_id !== place.subprogram_id || target.level_id !== place.level_id || (target.package_id ?? null) !== (props.scope === 'package' ? props.node.id : null)) return false;
    }
    if (kind !== 'materials') return true;
    if (props.scope === 'session') return item.usage !== 'reference' && (!props.usedSessionResources.includes(item.resource_id ?? item.id) || (props.node.material_versions ?? []).includes(item.id));
    return item.usage !== 'session';
});
const elementId = (field) => `learning-${props.node.id}-${field}`;
const label = (item) => item.display_title || item.title;
const lookup = (id) => props.catalog.find((item) => item.id === id);
function add(field, selection) { if (!selection) return; props.node[field] ??= []; if (!props.node[field].includes(selection)) props.node[field].push(selection); }
function addFramework() { if (!pickFramework.value) return; props.node.frameworks ??= []; if (!props.node.frameworks.some((v) => v.version_id === pickFramework.value)) props.node.frameworks.push({ version_id: pickFramework.value, levels: [] }); pickFramework.value = ''; }
function remove(field, i) { props.node[field].splice(i, 1); }
const choices = computed(() => kindItems('frameworks').filter((item) => !(props.node.frameworks ?? []).some((v) => v.version_id === item.id)));
</script>
<template>
    <div class="learning-links space-y-5 rounded-xl border border-line p-5">
        <template v-if="scope === 'level' && sections.includes('frameworks') && !sections.includes('materials')">
            <div><h4 class="text-base font-semibold text-brand">Modul</h4><p class="mt-2 text-xs text-muted">Cakupan pembelajaran</p></div>
            <div class="flex flex-wrap gap-2"><select :id="elementId('frameworks')" v-model="pickFramework" @change="addFramework" :aria-label="`Pilih acuan ${name}`" class="admin-input min-w-0 flex-1"><option value="">Pilih acuan…</option><option v-for="item in choices" :key="item.id" :value="item.id">{{ label(item) }}</option></select></div>
            <div v-for="(mapping, i) in node.frameworks ?? []" :key="mapping.version_id" class="rounded-lg border border-line bg-white/70 p-4"><div class="flex justify-between gap-3"><p class="text-xs font-semibold text-brand">{{ lookup(mapping.version_id) ? label(lookup(mapping.version_id)) : 'Acuan tidak tersedia' }}</p><button type="button" class="text-xs text-muted underline" @click="remove('frameworks', i)">Lepas</button></div><div class="mt-3 flex flex-wrap gap-4"><label v-for="level in lookup(mapping.version_id)?.levels ?? []" :key="level" class="flex items-center gap-2 text-xs"><input v-model="mapping.levels" type="checkbox" :value="level" class="accent-interactive" />{{ level }}</label></div></div>
        </template>
        <div v-if="sections.includes('materials') && sections.includes('frameworks')" class="space-y-4">
            <h4 class="text-base font-semibold text-brand">Modul</h4>
            <div><label :for="elementId('module-choice')" class="field-label">Pilih modul</label><select :id="elementId('module-choice')" v-model="pickMaterial" class="admin-input min-w-0 max-w-full" @change="chooseModule"><option value="">Pilih modul…</option><option v-for="item in moduleChoices" :key="item.id" :value="item.id">{{ label(item) }}</option></select></div>
            <p v-if="!selectedModules.length" class="text-xs text-muted">Pilih modul untuk melihat materi.</p>
            <div class="mt-3 space-y-3"><article v-for="item in selectedModules" :key="item.id" class="rounded-lg border border-line p-4">
                <div class="flex items-center justify-between gap-3"><h5 class="text-sm font-semibold text-brand">{{ label(item) }}</h5><button type="button" class="text-xs text-muted underline" @click="detachModule(item.selection_id)">Lepas</button></div>
                <p v-if="item.reference_labels?.length" class="mt-2 text-xs text-muted">Cakupan: {{ item.reference_labels.join('; ') }}</p>
                <a v-if="item.file_url || item.url" :href="item.file_url || item.url" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block text-xs font-semibold text-interactive">{{ item.file_url ? 'Unduh PDF' : 'Buka tautan' }}</a>
                <div v-if="item.attachments?.length" class="mt-3 text-xs"><p class="font-medium">Materi modul</p><ul class="mt-3 space-y-3"><li v-for="file in item.attachments" :key="file.id"><p class="font-medium">{{ file.title }}<span v-if="file.purpose" class="ml-2 font-normal text-muted">{{ file.purpose }}</span></p><p v-if="file.description" class="mt-1 text-muted">{{ file.description }}</p><a v-if="file.preview_url || file.url" :href="file.preview_url || file.url" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block font-semibold text-interactive">{{ file.preview_url ? 'Buka PDF' : 'Buka tautan' }}</a></li></ul></div>
            </article></div>
            <Link v-if="abilities['learning.manage']" :href="addModuleUrl" class="admin-secondary mt-4 inline-flex">Tambah modul</Link>

        </div>
        <div v-else-if="sections.includes('materials') && !sections.includes('frameworks')"><h4 class="text-sm font-semibold text-brand">{{ scope === 'session' ? 'Materi sesi terpilih' : scope === 'package' ? 'Referensi guru untuk paket' : 'Modul referensi level' }}</h4><p v-if="scope === 'level'" class="mt-1 text-xs leading-6 text-muted">Pilih modul dari Modul sebagai referensi untuk seluruh sesi pada level ini.</p><div class="mt-3 flex flex-wrap gap-2"><select :id="elementId('material_versions')" v-model="pickMaterial" :aria-label="`Pilih materi ${name}`" class="admin-input min-w-0 flex-1"><option value="">Pilih materi…</option><option v-for="item in kindItems('materials')" :key="item.id" :value="item.id">{{ label(item) }}</option></select><button type="button" class="admin-secondary" :disabled="!pickMaterial" @click="add('material_versions', pickMaterial); pickMaterial = ''">Hubungkan materi</button></div><ul class="mt-3 space-y-3"><li v-for="(id, i) in node.material_versions ?? []" :key="id" class="flex flex-wrap items-center justify-between gap-3 text-xs"><span>{{ lookup(id) ? label(lookup(id)) : 'Materi tidak tersedia' }} <a v-if="lookup(id)?.file_url || lookup(id)?.url" :href="lookup(id).file_url || lookup(id).url" target="_blank" rel="noopener noreferrer" class="ml-2 font-semibold text-interactive">{{ lookup(id).file_url ? 'Unduh PDF' : 'Buka tautan' }}</a><details v-if="lookup(id)?.attachments?.length" class="mt-2"><summary class="cursor-pointer text-interactive">Lihat {{ lookup(id).attachments.length }} berkas</summary><ul class="mt-3 space-y-3"><li v-for="file in lookup(id).attachments" :key="file.id"><p class="font-medium">{{ file.title }}<span v-if="file.purpose" class="ml-2 font-normal text-muted">{{ file.purpose }}</span></p><p v-if="file.description" class="mt-1 text-muted">{{ file.description }}</p><a v-if="file.preview_url || file.url" :href="file.preview_url || file.url" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block font-semibold text-interactive">{{ file.preview_url ? 'Buka PDF' : 'Buka tautan' }}</a></li></ul></details></span><button type="button" class="text-muted underline" @click="remove('material_versions', i)">Lepas</button></li></ul></div>
        <div v-if="scope !== 'session' && sections.includes('assessments')">
            <h4 class="text-sm font-semibold text-brand">{{ scope === 'package' ? 'Ujian Akhir Paket' : 'Ujian Akhir Level' }}</h4>
            <ul v-if="exams.length" class="mt-4 divide-y divide-line">
                <li v-for="item in exams" :key="item.id" class="flex flex-wrap items-center justify-between gap-4 py-4 text-sm">
                    <div><p class="font-semibold text-brand">{{ item.title }}</p><p class="mt-1 text-xs text-muted">{{ item.questions_count }} soal · Sesi {{ item.target.session_start }}–{{ item.target.session_end }}</p></div>
                    <Link v-if="abilities['learning.manage']" :href="`/admin/learning/assessments/${item.resource_id}/edit`" class="text-xs font-semibold text-interactive">Buka ujian</Link>
                </li>
            </ul>
            <p v-else class="mt-3 text-xs text-muted">Belum ada ujian siap dipakai untuk {{ scope === 'package' ? name : 'level ini' }}.</p>
            <p class="mt-3 text-xs leading-6 text-muted">Ujian ditampilkan otomatis sesuai tujuan yang ditetapkan di menu Ujian.</p>
            <div v-if="legacyExams.length" class="mt-4 border-t border-line pt-4"><p class="text-xs text-muted">Ujian lama yang belum memiliki tujuan</p><ul class="mt-2 space-y-2"><li v-for="item in legacyExams" :key="item.id" class="text-xs"><Link v-if="abilities['learning.manage']" :href="`/admin/learning/assessments/${item.resource_id}/edit`" class="text-interactive">{{ item.title }} · Atur tujuan</Link><span v-else>{{ item.title }}</span></li></ul></div>
        </div>
        <p v-if="!catalog.some((item) => sections.includes(item.kind))" class="text-xs leading-6 text-muted">Belum ada sumber siap dipakai. Tambahkan melalui menu Akademik.</p>
    </div>
</template>
