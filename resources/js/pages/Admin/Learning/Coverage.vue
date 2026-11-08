<script setup>
import { computed, nextTick, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import Field from '../../../components/ProgramField.vue';
import { useAdminFormBuffer } from '../../../utils/useAdminFormBuffer';
const props = defineProps({ coverage: Array });
const rows = ref([...props.coverage]);
const editingId = ref(null);
const search = ref('');
const showArchived = ref(false);
const message = ref('');
const form = useForm({ revision: 0, title: '', description: '', status: 'ready', levels: [], level_input: '' });
const buffer = useAdminFormBuffer('materials', form, { editingId });
const visible = computed(() => rows.value.filter((row) => (showArchived.value || !row.archived) && row.title.toLocaleLowerCase().includes(search.value.trim().toLocaleLowerCase())).sort((a, b) => a.title.localeCompare(b.title)));
function load(row = null) {
    if (form.processing) return;
    if (form.isDirty && !confirm('Abaikan perubahan cakupan yang belum disimpan?')) return;
    apply(row);
    nextTick(() => document.getElementById('coverage-title')?.focus());
}
function apply(row) {
    editingId.value = row?.id ?? null;
    Object.assign(form, { revision: row?.revision ?? 0, title: row?.title ?? '', description: row?.description ?? '', status: 'ready', levels: [...(row?.levels ?? [])], level_input: '' });
    form.clearErrors(); form.defaults(); buffer.saved();
}
function addLevel() {
    const text = form.level_input.trim();
    if (text && !form.levels.includes(text)) form.levels.push(text);
    form.level_input = '';
}
function replace(row) { const index = rows.value.findIndex((item) => item.id === row.id); if (index < 0) rows.value.push(row); else rows.value.splice(index, 1, row); }
async function request(url, data) {
    const token = document.cookie.split('; ').find((item) => item.startsWith('XSRF-TOKEN='))?.split('=').slice(1).join('=');
    const response = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-XSRF-TOKEN': decodeURIComponent(token ?? '') }, body: JSON.stringify(data) });
    const result = await response.json().catch(() => ({}));
    if (!response.ok) { form.setError(Object.fromEntries(Object.entries(result.errors ?? { coverage: [result.message || 'Belum berhasil disimpan.'] }).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value]))); return null; }
    return result;
}
async function save() {
    if (form.processing) return;
    addLevel(); form.clearErrors(); form.processing = true; message.value = '';
    try {
        const previous = form.revision;
        const result = await request(`/admin/learning/materials/coverage${editingId.value ? `/${editingId.value}` : ''}`, { revision: form.revision, title: form.title, description: form.description, status: 'ready', levels: form.levels });
        if (result) { replace(result); apply(result); message.value = previous === result.revision ? 'Tidak ada perubahan. Cakupan tetap sama.' : 'Cakupan berhasil disimpan.'; }
    } catch { form.setError('coverage', 'Koneksi gagal. Isian tetap tersedia, silakan coba kembali.'); }
    finally { form.processing = false; }
}
async function changeArchive(row, archive) {
    if (form.processing || !confirm(archive ? `Hapus cakupan ${row.title} dari pilihan baru? Modul dan riwayat yang sudah memakainya tetap tersimpan.` : `Pulihkan cakupan ${row.title}?`)) return;
    if (editingId.value === row.id && form.isDirty && !confirm('Abaikan perubahan cakupan ini yang belum disimpan?')) return;
    form.clearErrors(); form.processing = true;
    try {
        const result = await request(`/admin/learning/materials/coverage/${row.id}/${archive ? 'archive' : 'restore'}`, { revision: row.revision });
        if (result) { replace(result); if (editingId.value === row.id) apply(null); message.value = archive ? 'Cakupan dihapus dari pilihan baru. Riwayat tetap tersimpan.' : 'Cakupan berhasil dipulihkan.'; }
    } catch { form.setError('coverage', 'Koneksi gagal. Silakan coba kembali.'); }
    finally { form.processing = false; }
}
</script>
<template>
    <Head title="Kelola cakupan" /><AdminLayout>
        <Link href="/admin/learning/materials" class="text-xs text-interactive">Kembali ke Modul</Link>
        <div class="mt-5 flex flex-wrap items-center justify-between gap-4"><div><h1 class="text-2xl font-semibold text-brand">Cakupan modul</h1><p class="mt-2 text-sm text-muted">Kelola acuan seperti HSK, BCT, dan tingkatannya.</p></div><button type="button" class="admin-secondary" :disabled="form.processing" @click="load()">Tambah cakupan</button></div>
        <p v-if="message" role="status" class="mt-5 rounded-xl bg-brand-soft p-4 text-sm text-brand">{{ message }}</p>
        <div v-if="form.hasErrors" role="alert" class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-800"><p v-for="(error, key) in form.errors" :key="key">{{ error }}</p></div>
        <section class="learning-panel mt-6"><div class="learning-panel-heading"><h2>{{ editingId ? 'Ubah cakupan' : 'Tambah cakupan' }}</h2><button type="button" class="admin-button" :disabled="form.processing" @click="save">{{ form.processing ? 'Menyimpan…' : 'Simpan cakupan' }}</button></div><fieldset :disabled="form.processing" class="min-w-0 space-y-5 p-6"><div class="grid gap-5 sm:grid-cols-2"><Field v-model="form.title" name="coverage-title" label="Nama cakupan" placeholder="Contoh: HSK atau BCT" :error="form.errors.title" /><Field v-model="form.description" name="coverage-description" label="Keterangan" :error="form.errors.description" /></div><div><label for="coverage-level-input" class="field-label">Tingkatan baru</label><div class="flex flex-wrap gap-3"><input id="coverage-level-input" v-model="form.level_input" class="admin-input min-w-0 flex-1" placeholder="Contoh: HSK 1 atau BCT A" @keydown.enter.prevent="addLevel" /><button type="button" class="admin-secondary" @click="addLevel">Tambah tingkatan</button></div></div><div v-for="(_, i) in form.levels" :key="i" class="flex items-center gap-3"><input v-model="form.levels[i]" :aria-label="`Tingkatan ${i + 1}`" class="admin-input min-w-0 flex-1" /><button type="button" class="text-xs text-muted underline" :aria-label="`Hapus tingkatan ${i + 1}`" @click="form.levels.splice(i, 1)">Hapus</button></div><p class="text-xs leading-6 text-muted">Perubahan berlaku pada pilihan baru. Cakupan dalam riwayat modul sebelumnya tetap tersimpan.</p></fieldset></section>
        <section class="learning-panel mt-6"><div class="flex flex-wrap items-center gap-4 border-b border-line p-5"><input v-model="search" type="search" aria-label="Cari cakupan" placeholder="Cari cakupan…" class="admin-input max-w-sm" /><label class="flex items-center gap-2 text-xs"><input v-model="showArchived" type="checkbox" class="accent-interactive" />Tampilkan yang dihapus</label></div><div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-page text-xs text-muted"><tr><th class="px-5 py-4">Cakupan</th><th class="px-5 py-4">Tingkatan</th><th class="px-5 py-4">Tindakan</th></tr></thead><tbody class="divide-y divide-line"><tr v-for="row in visible" :key="row.id"><td class="px-5 py-4"><p class="font-semibold text-brand">{{ row.title }}</p><p v-if="row.archived" class="mt-1 text-xs text-muted">Dihapus dari pilihan</p></td><td class="px-5 py-4 text-xs text-muted">{{ row.levels.join(', ') || 'Tanpa tingkatan' }}</td><td class="px-5 py-4"><div class="flex gap-4"><template v-if="!row.archived"><button type="button" class="font-semibold text-interactive" :disabled="form.processing" @click="load(row)">Ubah</button><button type="button" class="text-red-700" :disabled="form.processing" @click="changeArchive(row, true)">Hapus</button></template><button v-else type="button" class="font-semibold text-interactive" :disabled="form.processing" @click="changeArchive(row, false)">Pulihkan</button></div></td></tr><tr v-if="!visible.length"><td colspan="3" class="px-5 py-10 text-center text-muted">Belum ada cakupan yang sesuai.</td></tr></tbody></table></div></section>
    </AdminLayout>
</template>
