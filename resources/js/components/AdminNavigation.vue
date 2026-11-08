<script setup>
import { computed, reactive, watch } from 'vue';
import { workspaceNavigationUrl } from '../utils/learningWorkspace';
import { Link } from '@inertiajs/vue3';
const props = defineProps({ items: { type: Array, default: () => [] }, path: String, prefix: String });
const active = (item) => item.path === '/admin' ? props.path === '/admin' : props.path.startsWith(item.path);
const dashboard = computed(() => props.items.filter((item) => item.key === 'dashboard'));
const groups = computed(() => [
    { key: 'academic', label: 'Akademik', items: props.items.filter((item) => !item.hidden && item.key !== 'dashboard' && item.group === 'workspace') },
    { key: 'system', label: 'Sistem', items: props.items.filter((item) => item.group === 'system') },
].filter((group) => group.items.length));
const expanded = reactive({ academic: false, system: false });
watch(() => props.path, () => { for (const group of groups.value) if (group.items.some(active)) expanded[group.key] = true; }, { immediate: true });
</script>
<template>
    <Link v-for="item in dashboard" :key="item.key" :href="workspaceNavigationUrl(item.path)" :aria-current="active(item) ? 'page' : undefined" :class="['nav-item', active(item) && 'is-active']">{{ item.label }}</Link>
    <div v-for="group in groups" :key="group.key" class="mt-2">
        <button type="button" class="nav-group" :aria-expanded="expanded[group.key]" :aria-controls="`${prefix}-${group.key}`" @click="expanded[group.key] = !expanded[group.key]"><span>{{ group.label }}</span><svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" :class="['nav-chevron', expanded[group.key] && 'is-open']"><path d="m9 5 7 7-7 7" /></svg></button>
        <div v-show="expanded[group.key]" :id="`${prefix}-${group.key}`" class="ml-3 mt-1 border-l border-line pl-2"><Link v-for="item in group.items" :key="item.key" :href="workspaceNavigationUrl(item.path)" :aria-current="active(item) ? 'page' : undefined" :class="['nav-item', active(item) && 'is-active']">{{ item.label }}</Link></div>
    </div>
</template>
