<script setup>
import { computed } from 'vue';
const props = defineProps({ modelValue: Object, options: Array, errors: Object });
const emit = defineEmits(['update:modelValue']);
const value = computed(() => props.modelValue ?? { internal: false, frameworks: [] });
const choices = computed(() => {
    const map = new Map();
    for (const item of props.options ?? []) { if (item.archived) continue; const key = item.resource_id ?? item.id; if (!map.has(key) || map.get(key).revision < item.revision) map.set(key, item); }
    for (const mapping of value.value.frameworks ?? []) { const selected = props.options?.find((item) => item.id === mapping.version_id); if (selected) map.set(selected.resource_id ?? selected.id, selected); }
    return [...map.values()];
});
const available = computed(() => choices.value.filter((item) => !mapping(item.id) && !item.archived));
const selected = computed(() => choices.value.filter((item) => mapping(item.id)));
function choose(event) {
    const id = event.target.value;
    if (available.value.some((item) => item.id === id)) toggle(id, true);
    event.target.value = '';
}
const mapping = (id) => value.value.frameworks?.find((item) => item.version_id === id);
function update(change) { const next = JSON.parse(JSON.stringify(value.value)); next.frameworks ??= []; change(next); emit('update:modelValue', next); }
function toggle(id, checked) { update((next) => { if (checked && !mapping(id)) next.frameworks.push({ version_id: id, levels: [] }); else if (!checked) next.frameworks = next.frameworks.filter((item) => item.version_id !== id); }); }
function toggleLevel(id, level, checked) { update((next) => { const item = next.frameworks.find((v) => v.version_id === id); if (checked && !item.levels.includes(level)) item.levels.push(level); else if (!checked) item.levels = item.levels.filter((v) => v !== level); }); }
const messages = computed(() => [...new Set(Object.entries(props.errors ?? {}).filter(([key]) => key === 'references' || key.startsWith('references.')).map(([, text]) => text))]);
</script>
<template>
    <section class="learning-panel">
        <div class="learning-panel-heading">
            <div><h2>Cakupan modul</h2><p>Pilih cakupan, lalu tentukan tingkatannya.</p></div>
        </div>
        <div class="space-y-4 p-6">
            <div>
                <label for="module-coverage-picker" class="field-label">Pilih cakupan</label>
                <select id="module-coverage-picker" class="admin-input w-full" :disabled="!available.length" @change="choose">
                    <option value="">Pilih cakupan…</option>
                    <option v-for="item in available" :key="item.id" :value="item.id">{{ item.title }}</option>
                </select>
            </div>
            <div v-for="item in selected" :key="item.id" class="rounded-lg border border-line p-4">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold">{{ item.title }} <span v-if="item.archived" class="text-xs font-normal text-muted">Dihapus</span></h3>
                    <button type="button" class="text-xs text-muted underline" :aria-label="`Lepas cakupan ${item.title}`" @click="toggle(item.id, false)">Lepas</button>
                </div>
                <div v-if="item.levels.length" class="mt-4 flex flex-wrap gap-4 border-t border-line pt-4">
                    <label v-for="level in item.levels" :key="level" class="flex items-center gap-2 text-sm">
                        <input type="checkbox" class="accent-interactive" :disabled="item.archived" :checked="mapping(item.id).levels.includes(level)" @change="toggleLevel(item.id, level, $event.target.checked)" />{{ level }}
                    </label>
                </div>
            </div>
            <p v-if="!choices.length" class="text-xs text-muted">Belum ada cakupan tersedia.</p>
            <p v-for="message in messages" :key="message" role="alert" class="field-error">{{ message }}</p>
        </div>
    </section>
</template>
