<script setup>
import { computed, ref } from 'vue';
const props = defineProps({ node: Object, name: String, catalog: { type: Array, default: () => [] } });
const emit = defineEmits(['uploaded', 'busy']);
const selected = ref(null);
const input = ref(null);
const busy = ref(false);
const failure = ref('');
const message = ref('');
const materials = computed(() => (props.node.material_versions ?? []).map((id) => ({ id, ...(props.catalog.find((item) => item.id === id) ?? { title: 'Materi tersimpan' }) })));
function remove(id) {
    if (busy.value || !confirm('Lepas materi dari sesi ini? Berkas tetap tersimpan.')) return;
    props.node.material_versions = props.node.material_versions.filter((value) => value !== id);
    selected.value = null; failure.value = ''; message.value = '';
}
async function upload() {
    if (!selected.value || busy.value || materials.value.length) return;
    failure.value = ''; message.value = '';
    if (selected.value.size > 2 * 1024 * 1024) { failure.value = 'PDF maksimal 2 MB.'; return; }
    busy.value = true; emit('busy', true);
    try {
        const body = new FormData(); body.append('file', selected.value); body.append('title', props.node.title || props.name);
        const token = document.cookie.split('; ').find((item) => item.startsWith('XSRF-TOKEN='))?.split('=').slice(1).join('=');
        const response = await fetch('/admin/learning/session-materials', { method: 'POST', headers: { Accept: 'application/json', 'X-XSRF-TOKEN': decodeURIComponent(token ?? '') }, body });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) { failure.value = Object.values(result.errors ?? {}).flat().join(' ') || 'Unggahan belum berhasil. Pilih berkas dan coba kembali.'; return; }
        props.node.material_versions = [result.id];
        emit('uploaded', result);
        selected.value = null; if (input.value) input.value.value = '';
        message.value = 'PDF ditambahkan ke sesi ini. Terbitkan perubahan untuk menyimpannya.';
    } catch { failure.value = 'Koneksi gagal. Coba unggah kembali.'; }
    finally { busy.value = false; emit('busy', false); }
}
</script>
<template>
    <section class="rounded-xl border border-line p-4" :aria-label="`Berkas ${name}`">
        <h4 class="field-label">PDF materi {{ name }}</h4>
        <template v-if="!materials.length">
            <p class="mb-3 text-xs text-muted">PDF, maksimal 2 MB.</p>
            <div class="flex flex-wrap items-center gap-3"><input :id="`session-pdf-${node.id}`" ref="input" :aria-label="`PDF materi ${name}`" type="file" accept="application/pdf,.pdf" class="admin-input min-w-0 flex-1" @change="selected = $event.target.files[0] ?? null; upload()" /><button v-if="failure && selected && !busy" type="button" class="admin-secondary" @click="upload">Coba unggah lagi</button></div>
        </template>
        <div v-for="material in materials" :key="material.id" class="mt-3">
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs"><p class="min-w-0 break-words font-medium text-ink">{{ material.title }}</p><div class="flex flex-wrap items-center gap-4"><a v-if="material.preview_url" :href="material.preview_url" target="_blank" rel="noopener noreferrer" class="font-semibold text-interactive">Buka PDF</a><a v-if="material.file_url || material.url" :href="material.file_url || material.url" target="_blank" rel="noopener noreferrer" class="font-semibold text-interactive">{{ material.file_url ? 'Unduh PDF' : 'Buka tautan' }}</a><button type="button" class="text-muted underline" :aria-label="`Lepas materi ${material.title} dari sesi`" @click="remove(material.id)">Lepas</button></div></div>
            <iframe v-if="material.preview_url" :src="material.preview_url" :title="`Pratinjau PDF ${material.title}`" class="mt-4 h-[440px] w-full rounded-lg border border-line bg-page sm:h-[560px]" loading="lazy" referrerpolicy="same-origin"></iframe>
            <p v-if="material.preview_url" class="mt-2 text-xs text-muted">Jika pratinjau tidak tampil, pilih Buka PDF atau Unduh PDF.</p>
        </div>
        <p v-if="busy" role="status" class="mt-3 text-xs text-muted">Sedang mengunggah PDF…</p><p v-if="failure" role="alert" class="field-error">{{ failure }}</p><p v-if="message" role="status" class="mt-3 text-xs leading-6 text-muted">{{ message }}</p>
    </section>
</template>
