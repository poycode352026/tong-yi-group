<script setup>
import { computed, onBeforeUnmount } from 'vue';
const props = defineProps({ question: Object, index: Number, errors: Object });
const urls = new Map();
const sources = computed(() => Object.fromEntries(['image', 'audio'].map((kind) => {
    const file = props.question[`${kind}_file`];
    if (file instanceof File) {
        if (!urls.has(file)) urls.set(file, URL.createObjectURL(file));
        return [kind, urls.get(file)];
    }
    return [kind, props.question[`remove_${kind}`] ? null : props.question.media?.[kind]?.url];
})));
function choose(event, kind) {
    props.question[`${kind}_file`] = event.target.files[0] ?? null;
    if (props.question[`${kind}_file`]) props.question[`remove_${kind}`] = false;
}
function remove(kind) { props.question[`${kind}_file`] = null; props.question[`remove_${kind}`] = true; }
onBeforeUnmount(() => { for (const url of urls.values()) URL.revokeObjectURL(url); });
</script>
<template>
    <div class="grid gap-5 sm:grid-cols-2">
        <div v-for="kind in ['image', 'audio']" :key="kind" class="min-w-0 space-y-3 rounded-lg border border-line p-4">
            <label :for="`question-${question.id}-${kind}`" class="field-label">{{ kind === 'image' ? 'Gambar soal (opsional)' : 'Audio soal (opsional)' }}</label>
            <input :id="`question-${question.id}-${kind}`" :key="`${question.id}-${kind}-${question[`remove_${kind}`]}`" type="file" :accept="kind === 'image' ? '.jpg,.jpeg,.png,.webp' : '.mp3,.wav,.m4a,.ogg'" class="admin-input w-full min-w-0" @change="choose($event, kind)" />
            <p class="text-xs text-muted">{{ kind === 'image' ? 'JPG, PNG, WebP' : 'MP3, WAV, M4A, OGG' }} · maksimal 2 MB.</p>
            <template v-if="sources[kind]">
                <img v-if="kind === 'image'" :src="sources[kind]" :alt="`Gambar soal ${index + 1}`" class="max-h-64 max-w-full rounded-lg object-contain" />
                <audio v-else :src="sources[kind]" controls preload="metadata" class="w-full" />
                <button type="button" class="text-xs text-red-700" @click="remove(kind)">Lepas {{ kind === 'image' ? 'gambar' : 'audio' }}</button>
            </template>
            <p v-if="errors?.[`questions.${index}.${kind}_file`]" class="field-error">{{ errors[`questions.${index}.${kind}_file`] }}</p>
        </div>
    </div>
</template>
