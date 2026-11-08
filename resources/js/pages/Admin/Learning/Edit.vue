<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import { setWorkspaceBusy, isWorkspaceReload, adminWorkspace, setWorkspaceDirty, isAdminDestination, ensureLearningWorkspace, learningDraft, storeLearningDraft, clearLearningDraft, copyLearningData } from '../../../utils/learningWorkspace';
import QuestionMedia from '../../../components/QuestionMedia.vue';
import ExamPlacement from '../../../components/ExamPlacement.vue';
import ModuleEditor from '../../../components/ModuleEditor.vue';
import Field from '../../../components/ProgramField.vue';
const props = defineProps({ kind: String, resource: Object, examPrograms: Array, moduleContext: Object, frameworkOptions: Array });
const labels = { frameworks: 'acuan', materials: 'modul', assessments: 'ujian' };
ensureLearningWorkspace(usePage().props.auth?.user);
let current = props.resource ?? {};
const draftKey = `${props.kind}:${current.id ?? 'new'}`;
const inlineKey = props.kind === 'materials' && !current.id && props.moduleContext?.level ? `programs:module-${props.moduleContext.level}` : null;
const pendingInline = inlineKey && !learningDraft(draftKey) ? learningDraft(inlineKey) : null;
const suspended = learningDraft(draftKey) ?? (pendingInline ? { ...pendingInline, errors: {} } : null);

const form = useForm({ revision: current.revision ?? 0, title: current.title ?? '', description: current.description ?? '', edition: current.edition ?? '', status: current.status ?? 'draft',
    ...(props.kind === 'frameworks' ? { levels: current.levels ?? [] } : {}),
    ...(props.kind === 'materials' ? { references: current.references ?? { internal: false, frameworks: [] }, format: 'module_catalog', author: current.author ?? '', attachments: JSON.parse(JSON.stringify(current.attachments ?? [{ id: crypto.randomUUID(), title: '', purpose: '', description: '', source: 'pdf', url: '', file_id: null, legacy_version_id: null }])).map((row) => ({ ...row, file: null })) } : {}),
    ...(props.kind === 'assessments' ? { scope: current.scope ?? 'package', target: { program_id: current.target?.program_id ?? '', program_revision: props.examPrograms?.find((p) => p.id === current.target?.program_id)?.revision ?? null, subprogram_id: current.target?.subprogram_id ?? '', level_id: current.target?.level_id ?? '', package_id: current.target?.package_id ?? '' }, questions: JSON.parse(JSON.stringify(current.questions ?? [])) } : {}),
});
let bufferDefaults = suspended?.defaults ?? copyLearningData(form.data());
if (suspended) {
    form.defaults(copyLearningData(suspended.defaults ?? bufferDefaults));
    Object.assign(form, copyLearningData(suspended.data));
    form.setError(copyLearningData(suspended.errors ?? {}));
}
if (pendingInline) { storeLearningDraft(draftKey, { data: copyLearningData(form.data()), defaults: copyLearningData(bufferDefaults), errors: copyLearningData(form.errors), missingFiles: pendingInline.missingFiles ?? [] }); clearLearningDraft(inlineKey); }
if (props.kind === 'materials') form.format = 'module_catalog';
function suspendForm() {
    if (form.isDirty || form.hasErrors) storeLearningDraft(`${props.kind}:${current.id ?? 'new'}`, { data: copyLearningData(form.data()), defaults: copyLearningData(bufferDefaults), errors: copyLearningData(form.errors), scroll: window.scrollY });
    else clearLearningDraft(`${props.kind}:${current.id ?? 'new'}`);
}
onMounted(async () => { if (suspended) { await nextTick(); window.scrollTo({ top: suspended.scroll ?? 0, behavior: 'instant' }); } });
watch(() => form.processing, (busy) => setWorkspaceBusy(props.kind, busy), { immediate: true });
watch(() => form.isDirty, (dirty) => setWorkspaceDirty(props.kind, dirty), { immediate: true, flush: 'sync' });
watch(() => form.source, (source) => { if (source === 'link') form.file = null; });
function selectPdf(event) {
    const file = event.target.files[0] ?? null;
    if (file && file.size > 2 * 1024 * 1024) {
        form.setError('file', 'PDF maksimal 2 MB untuk pengembangan lokal.');
        form.file = null;
        event.target.value = '';
        return;
    }
    form.clearErrors('file');
    form.file = file;
}
const newLevel = ref('');
const allowLeave = ref(false);
function addLevel() { const value = newLevel.value.trim(); if (value && !form.levels.includes(value)) { form.levels.push(value); newLevel.value = ''; } }
function removeLevel(i) { if (confirm('Keluarkan tingkatan ini dari versi baru? Versi terdahulu tetap disimpan.')) form.levels.splice(i, 1); }
function addQuestion(type) { form.questions.push({ id: crypto.randomUUID(), type, prompt: '', options: type === 'choice' ? ['', ''] : [], answer: null, guide: '' }); }
function removeQuestion(i) { if (confirm('Keluarkan soal ini dari versi baru? Versi terdahulu tetap disimpan.')) form.questions.splice(i, 1); }
function removeOption(q, i) { q.options.splice(i, 1); if (q.answer === i) q.answer = null; else if (q.answer > i) q.answer--; }
function save(status) {
    if (props.kind === 'materials' && form.attachments.reduce((sum, row) => sum + (row.file?.size ?? 0), 0) > 6 * 1024 * 1024) { form.setError('attachments', 'Total unggahan maksimal 6 MB sekali simpan. Simpan sebagian berkas dahulu.'); return; }
    if (props.kind === 'assessments') {
        const files = form.questions.flatMap((q) => [q.image_file, q.audio_file]).filter((file) => file instanceof File);
        if (files.length > 20 || files.reduce((total, file) => total + file.size, 0) > 6 * 1024 * 1024) { form.setError('questions', 'Unggah maksimal 20 media dengan total 6 MB sekali simpan. Simpan sebagian media terlebih dahulu.'); return; }
    }
    form.status = status;
    allowLeave.value = true;
    form.post(`/admin/learning/${props.kind}${current.id ? `/${current.id}` : ''}`, { forceFormData: true, preserveScroll: true, onSuccess: () => { if (suspended) suspended.missingFiles = []; clearLearningDraft(draftKey); clearLearningDraft(`${props.kind}:${current.id ?? 'new'}`); current = props.resource; form.revision = current.revision; if ('file' in form) form.file = null; if (props.kind === 'materials') { form.attachments = (current.attachments ?? []).map((row) => ({ ...row, file: null })); } if (props.kind === 'assessments') form.questions = JSON.parse(JSON.stringify(current.questions ?? [])); form.defaults(); bufferDefaults = copyLearningData(form.data()); allowLeave.value = false; }, onError: () => { allowLeave.value = false; }, onFinish: () => { allowLeave.value = false; } });
}
const title = computed(() => `${props.resource?.id ? 'Ubah' : 'Tambah'} ${labels[props.kind]}`);
const stop = router.on('before', (event) => {
    if (form.processing && event.detail.visit.method.toLowerCase() === 'get') { event.preventDefault(); return; }
    if (adminWorkspace.closing === props.kind || allowLeave.value) return;
    const withinWorkspace = isAdminDestination(event.detail.visit.url);
    if (withinWorkspace) { suspendForm(); return; }
    if (form.isDirty && !confirm('Ada perubahan belum tersimpan. Tinggalkan halaman ini?')) event.preventDefault();
    else suspendForm();
});
const beforeUnload = (event) => { suspendForm(); if (!isWorkspaceReload() && form.isDirty && !allowLeave.value) { event.preventDefault(); event.returnValue = ''; } };
if (typeof window !== 'undefined') { window.addEventListener('beforeunload', beforeUnload); window.addEventListener('tong-yi:workspace-suspend', suspendForm); }
onBeforeUnmount(() => { stop(); window.removeEventListener('beforeunload', beforeUnload); window.removeEventListener('tong-yi:workspace-suspend', suspendForm); });
</script>
<template>
    <Head :title="title" /><AdminLayout>
        <Link :href="`/admin/learning/${kind}`" class="text-xs text-muted">Kembali ke {{ labels[kind] }}</Link>
        <div class="learning-intro mt-5 rounded-2xl border border-line p-6"><h1 class="mt-2 text-2xl font-semibold text-brand">{{ title }}</h1><p class="mt-2 text-sm text-muted">{{ current.id ? `Versi tersimpan v${current.revision}. Versi baru dibuat hanya jika ada perubahan.` : 'Lengkapi isian, lalu simpan saat siap dipakai.' }}</p></div>
        <div class="learning-toolbar sticky top-[76px] z-20 my-5 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-line px-5 py-4"><p class="text-xs text-muted">{{ form.isDirty ? 'Ada perubahan belum tersimpan' : 'Isian tersimpan / belum diubah' }}</p><div class="flex flex-wrap gap-3"><button type="button" class="admin-button" :disabled="form.processing" @click="save('ready')">{{ form.processing ? 'Menyimpan…' : 'Simpan & siap dipakai' }}</button></div></div>
        <p v-if="suspended?.missingFiles?.length" role="alert" class="mb-5 text-xs text-muted">Pilih kembali berkas yang belum sempat diunggah: {{ suspended.missingFiles.join(', ') }}. Isian lain sudah dipulihkan.</p><p v-if="suspended && form.isDirty" role="status" class="mb-5 text-xs text-muted">Isian tab dipulihkan. Belum tersimpan ke server.</p><p v-if="usePage().props.flash?.success" role="status" class="mb-5 rounded-xl bg-brand-soft p-4 text-sm text-brand">{{ usePage().props.flash.success }}</p>
        <div v-if="form.hasErrors" role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-800"><p class="font-semibold">Periksa isian sebelum disimpan.</p><ul class="mt-2 list-inside list-disc"><li v-for="(error, key) in form.errors" :key="key">{{ error }}</li></ul></div>
        <form class="space-y-6" @submit.prevent><fieldset :disabled="form.processing" class="space-y-6">
            <section v-if="kind !== 'materials'" class="learning-panel"><div class="learning-panel-heading"><h2>Identitas {{ labels[kind] }}</h2></div><div class="grid gap-5 p-6 sm:grid-cols-2"><Field v-model="form.title" name="title" label="Judul / nama" :error="form.errors.title" class="sm:col-span-2" :placeholder="kind === 'frameworks' ? 'Contoh: HSK' : kind === 'materials' ? 'Contoh: Modul Lengkap HSK 1' : 'Contoh: Ujian Paket 1'" /><Field v-model="form.description" name="description" type="textarea" :rows="3" label="Keterangan" :error="form.errors.description" class="sm:col-span-2" /></div></section>
            <section v-if="kind === 'frameworks'" class="learning-panel"><div class="learning-panel-heading"><h2>Tingkatan acuan</h2><p>Bebas angka atau teks, misalnya HSK 1, HSK 2, atau tingkat Dasar.</p></div><div class="p-6"><div class="flex flex-wrap gap-3"><input v-model="newLevel" aria-label="Tingkatan baru" class="admin-input max-w-sm" placeholder="Contoh: HSK 1" @keydown.enter.prevent="addLevel" /><button type="button" class="admin-secondary" @click="addLevel">Tambah tingkatan</button></div><ul class="mt-4 divide-y divide-line"><li v-for="(level, i) in form.levels" :key="level" class="flex items-center justify-between gap-3 py-3 text-sm"><span>{{ level }}</span><button type="button" class="text-xs text-red-700" @click="removeLevel(i)">Keluarkan</button></li></ul><p class="mt-3 text-xs leading-6 text-muted">Cakupan acuan bukan pernyataan kesetaraan atau kelulusan ujian resmi.</p></div></section>
            <ModuleEditor v-if="kind === 'materials'" :form="form" :options="frameworkOptions ?? []" />
            <template v-if="kind === 'assessments'">
                <ExamPlacement :form="form" :programs="examPrograms" />
                <section class="learning-panel"><div class="learning-panel-heading"><div><h2>Soal ujian</h2><p>Isian diperiksa petugas dengan acuan jawaban. Aturan skor dan kelulusan belum ditetapkan.</p></div><div class="flex flex-wrap gap-2"><button type="button" class="admin-secondary" @click="addQuestion('choice')">Tambah pilihan ganda</button><button type="button" class="admin-secondary" @click="addQuestion('text')">Tambah isian</button></div></div><div class="space-y-5 p-6"><p v-if="!form.questions.length" class="py-6 text-center text-sm text-muted">Belum ada soal. Tambahkan jenis soal yang diperlukan.</p><article v-for="(q, i) in form.questions" :key="q.id" class="question-panel space-y-5 rounded-xl border border-line p-5"><div class="flex items-center justify-between gap-3"><h3 class="text-sm font-semibold text-brand">Soal {{ i + 1 }} · {{ q.type === 'choice' ? 'Pilihan ganda' : 'Isian' }}</h3><button type="button" class="text-xs text-red-700" @click="removeQuestion(i)">Keluarkan soal</button></div><Field v-model="q.prompt" :name="`questions.${i}.prompt`" label="Pertanyaan / instruksi" type="textarea" :rows="3" :error="form.errors[`questions.${i}.prompt`]" /><QuestionMedia :question="q" :index="i" :errors="form.errors" /><div v-if="q.type === 'choice'" class="space-y-3"><p class="field-label">Pilihan jawaban · tandai satu kunci</p><div v-for="(_, j) in q.options" :key="j" class="flex items-center gap-3"><input v-model="q.answer" type="radio" :name="`key-${q.id}`" :value="j" :aria-label="`Kunci soal ${i + 1} pilihan ${j + 1}`" class="h-4 w-4 shrink-0 accent-interactive" /><input v-model="q.options[j]" class="admin-input" :aria-label="`Soal ${i + 1} pilihan ${j + 1}`" :placeholder="`Pilihan ${j + 1}`" /><button v-if="q.options.length > 2" type="button" class="text-xs text-muted" :aria-label="`Keluarkan pilihan ${j + 1}`" @click="removeOption(q, j)">Keluarkan</button></div><button v-if="q.options.length < 6" type="button" class="text-xs font-semibold text-interactive" @click="q.options.push('')">Tambah pilihan</button><p v-if="form.errors[`questions.${i}.options`] || form.errors[`questions.${i}.answer`]" class="field-error">{{ form.errors[`questions.${i}.options`] || form.errors[`questions.${i}.answer`] }}</p></div><Field v-model="q.guide" :name="`questions.${i}.guide`" :label="q.type === 'text' ? 'Acuan jawaban / pemeriksaan isian' : 'Penjelasan jawaban (opsional)'" type="textarea" :rows="2" :error="form.errors[`questions.${i}.guide`]" hint="Khusus petugas berwenang; tidak dikirim melalui pemilih materi program." /></article></div></section>
            </template>
        </fieldset></form>
    </AdminLayout>
</template>
