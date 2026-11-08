<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { linkPreview } from '../utils/linkPreview.js';
const props = defineProps({ item: Object });
const localPdf = ref(null);
function releasePdf() { if (localPdf.value) URL.revokeObjectURL(localPdf.value); localPdf.value = null; }
watch(() => props.item.file, (file) => {
    releasePdf();
    if (typeof File !== 'undefined' && file instanceof File) localPdf.value = URL.createObjectURL(file);
}, { immediate: true });
onBeforeUnmount(releasePdf);
const pdf = computed(() => props.item.source === 'pdf' ? localPdf.value || props.item.preview_url : null);
const link = computed(() => props.item.source === 'link' ? linkPreview(props.item.url) : null);
</script>
<template>
    <section v-if="pdf" class="min-w-0 overflow-hidden rounded-xl border border-line" aria-label="Pratinjau PDF">
        <div class="flex flex-wrap items-center justify-between gap-3 bg-page px-4 py-3"><div class="min-w-0"><h4 class="text-xs font-semibold text-brand">Pratinjau PDF</h4><p class="mt-1 break-words text-xs text-muted">{{ item.file?.name || item.file_name || item.title }}</p></div><a :href="pdf" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-interactive">Buka PDF</a></div>
        <iframe :src="pdf" :title="`Pratinjau PDF: ${item.title || item.file_name || 'Bahan modul'}`" loading="lazy" class="h-[360px] w-full border-0 bg-white sm:h-[460px]" />
        <p class="border-t border-line px-4 py-2 text-xs text-muted">Jika pratinjau tidak tampil di perangkat ini, gunakan Buka PDF.</p>
    </section>
    <section v-else-if="link" class="min-w-0 rounded-xl border border-line bg-page p-4" aria-label="Pratinjau tautan">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">Pratinjau tautan</p>
        <a :href="link.href" target="_blank" rel="noopener noreferrer" class="mt-2 block min-w-0"><p class="break-all text-xs text-muted">{{ link.domain }}</p><h4 class="mt-1 break-words text-sm font-semibold text-interactive">{{ item.title?.trim() || link.domain }}</h4><p v-if="item.description" class="mt-2 line-clamp-3 whitespace-pre-wrap text-xs leading-6 text-muted">{{ item.description }}</p><p class="mt-2 break-all text-xs text-muted">{{ link.path }}</p><span class="mt-3 inline-block text-xs font-semibold text-interactive">Buka tautan</span></a>
    </section>
</template>
