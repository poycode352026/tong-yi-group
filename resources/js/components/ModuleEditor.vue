<script setup>
import { computed } from 'vue';
import MaterialPreview from './MaterialPreview.vue';
import Field from './ProgramField.vue';
import ModuleCoverage from './ModuleCoverage.vue';
const props = defineProps({ form: Object, options: Array });
const coverageChoices = computed(() => (props.form.references?.frameworks ?? []).flatMap((mapping) => {
    const source = props.options?.find((item) => item.id === mapping.version_id);
    return (mapping.levels.length ? mapping.levels : ['']).map((level) => ({ key: JSON.stringify([mapping.version_id, level]), label: level || source?.title || 'Cakupan', version: mapping.version_id, level }));
}));
function coverageValue(row) {
    if (!row.references) return '';
    const refs = row.references.frameworks ?? [];
    if (refs.length === 1 && refs[0].levels.length <= 1) return JSON.stringify([refs[0].version_id, refs[0].levels[0] ?? '']);
    return 'legacy';
}
function setCoverage(row, key) {
    if (!key) { row.references = null; return; }
    const choice = coverageChoices.value.find((item) => item.key === key);
    if (choice) row.references = { internal: false, frameworks: [{ version_id: choice.version, levels: choice.level ? [choice.level] : [] }] };
}
function add() { props.form.attachments.push({ id: crypto.randomUUID(), title: '', purpose: '', description: '', source: 'pdf', url: '', file: null, file_id: null, legacy_version_id: null, references: null }); }
function remove(index) { if (confirm('Keluarkan berkas dari versi modul ini? Versi dan berkas sebelumnya tetap tersimpan.')) props.form.attachments.splice(index, 1); }
function choose(event, row, index) {
    const file = event.target.files[0] ?? null;
    if (file && file.size > 2 * 1024 * 1024) { props.form.setError(`attachments.${index}.file`, 'PDF maksimal 2 MB per berkas.'); row.file = null; event.target.value = ''; return; }
    props.form.clearErrors(`attachments.${index}.file`); row.file = file;
    if (file && !row.title) row.title = file.name.replace(/\.pdf$/i, '');
}
</script>
<template>
    <section class="learning-panel"><div class="learning-panel-heading"><h2>Identitas modul</h2></div><div class="grid gap-5 p-6 sm:grid-cols-2">
        <Field v-model="form.title" name="module-title" label="Nama modul" placeholder="Contoh: Absolute Beginner - Beginner" :error="form.errors.title" class="sm:col-span-2" />
        <Field v-model="form.description" name="module-description" label="Keterangan modul" type="textarea" :rows="2" class="sm:col-span-2" />
        <Field v-model="form.author" name="module-author" label="Penulis / pengelola" class="sm:col-span-2" />
    </div></section>
    <ModuleCoverage v-model="form.references" :options="options ?? []" :errors="form.errors" />
    <section class="learning-panel"><div class="learning-panel-heading"><div><h2>Bahan modul</h2><p>Satu modul dapat berisi beberapa berkas dengan fungsi berbeda.</p></div><button type="button" class="admin-secondary" @click="add">Tambah bahan</button></div><div class="space-y-4 p-6"><p class="text-xs leading-6 text-muted">Maksimal 2 MB per PDF dan total 6 MB unggahan sekali simpan. Berkas berikutnya dapat ditambahkan setelah menyimpan.</p><details v-for="(row, i) in form.attachments" :key="row.id" open class="rounded-xl border border-line"><summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-brand">{{ i + 1 }}. {{ row.title || 'Berkas baru' }}<span v-if="row.purpose" class="ml-2 text-xs font-normal text-muted">{{ row.purpose }}</span></summary><div class="space-y-4 border-t border-line p-5"><div class="grid gap-4 sm:grid-cols-2"><Field v-model="row.title" :name="`attachments.${i}.title`" label="Nama berkas" :error="form.errors[`attachments.${i}.title`]" /><Field v-model="row.purpose" :name="`attachments.${i}.purpose`" label="Fungsi berkas" placeholder="Contoh: Buku utama atau panduan pengajar" /><Field v-model="row.description" :name="`attachments.${i}.description`" label="Deskripsi berkas" type="textarea" :rows="2" class="sm:col-span-2" /></div><div><label :for="`module-source-${row.id}`" class="field-label">Jenis sumber</label><select :id="`module-source-${row.id}`" v-model="row.source" class="admin-input max-w-sm" @change="row.file = null"><option value="pdf">PDF</option><option value="link">Tautan</option></select></div><template v-if="row.source === 'pdf'"><label :for="`module-file-${row.id}`" class="field-label">PDF berkas {{ i + 1 }}</label><input :id="`module-file-${row.id}`" type="file" accept="application/pdf,.pdf" class="admin-input" @change="choose($event, row, i)" /><p v-if="row.file" class="text-xs text-brand">Dipilih: {{ row.file.name }}</p><p v-else-if="row.file_name" class="text-xs text-muted">{{ row.file_name }} · kosongkan untuk mempertahankan berkas.</p><a v-if="row.file_url" :href="row.file_url" target="_blank" rel="noopener noreferrer" class="inline-block text-xs font-semibold text-interactive">Unduh PDF tersimpan</a><p v-if="form.errors[`attachments.${i}.file`]" class="field-error">{{ form.errors[`attachments.${i}.file`] }}</p></template><Field v-else v-model="row.url" :name="`attachments.${i}.url`" label="Tautan HTTPS" :error="form.errors[`attachments.${i}.url`]" /><MaterialPreview :item="row" /><div><label :for="`module-coverage-${row.id}`" class="field-label">Cakupan bahan</label><select :id="`module-coverage-${row.id}`" class="admin-input w-full" :value="coverageValue(row)" @change="setCoverage(row, $event.target.value)"><option value="">Mengikuti seluruh cakupan modul</option><option v-for="choice in coverageChoices" :key="choice.key" :value="choice.key">{{ choice.label }}</option><option v-if="row.references && !coverageChoices.some((choice) => choice.key === coverageValue(row))" :value="coverageValue(row)" disabled>Cakupan tersimpan — pilih ulang jika ingin mengubah</option></select><p v-for="(error, key) in Object.fromEntries(Object.entries(form.errors).filter(([key]) => key.startsWith(`attachments.${i}.references`)))" :key="key" class="field-error">{{ error }}</p></div><button type="button" class="text-xs text-red-700" @click="remove(i)">Keluarkan berkas</button></div></details><p v-if="!form.attachments.length" class="py-5 text-center text-sm text-muted">Tambahkan berkas pertama.</p></div></section>
</template>
