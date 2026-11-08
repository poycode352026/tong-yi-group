<script setup>
import { nextTick, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { adminWorkspace, registerAdminPage, workspaceDirty, workspaceTabDirty, dropWorkspaceTab } from '../utils/learningWorkspace';
const page = usePage();
watch(() => [page.url, page.props.adminNavigation], () => registerAdminPage(page.props.adminNavigation ?? [], page.url, page.props.version?.kind), { immediate: true });
function close(tab) {
    if (adminWorkspace.busy[adminWorkspace.active]) return;
    if (workspaceDirty(tab.key) && !confirm(`Tutup tab ${tab.label} dan abaikan isian yang belum disimpan?`)) return;
    if (adminWorkspace.active !== tab.key) { dropWorkspaceTab(tab.key); return; }
    const index = adminWorkspace.tabs.findIndex((item) => item.key === tab.key);
    const next = adminWorkspace.tabs[index - 1] ?? adminWorkspace.tabs[index + 1];
    // Dasbor menjadi tempat kembali saat tab terakhir ditutup.
    if (!next && tab.key === 'dashboard') { dropWorkspaceTab(tab.key); return; }
    adminWorkspace.closing = tab.key;
    router.visit(next?.href ?? '/admin', {
        onSuccess: async () => { dropWorkspaceTab(tab.key); await nextTick(); document.querySelector('[data-active-workspace]')?.focus(); },
        onFinish: () => { adminWorkspace.closing = null; },
        onCancel: () => { adminWorkspace.closing = null; },
    });
}
</script>
<template>
    <nav aria-label="Tab kerja Admin" class="admin-tabs relative mb-6 flex max-w-full gap-1 overflow-x-auto border-b border-line pb-px">
        <div v-for="tab in adminWorkspace.tabs" :key="tab.key" :class="['admin-tab flex shrink-0 items-center rounded-t-lg border border-b-0', adminWorkspace.active === tab.key ? 'is-active' : 'border-transparent']">
            <Link :href="tab.href" :data-active-workspace="adminWorkspace.active === tab.key ? '' : undefined" :aria-current="adminWorkspace.active === tab.key ? 'page' : undefined" class="flex items-center gap-2 px-3 py-2.5 text-xs font-medium"><span>{{ tab.label }}</span><span v-if="workspaceTabDirty(tab.key)" class="h-1.5 w-1.5 rounded-full bg-accent"><span class="sr-only">belum disimpan</span></span></Link>
            <button type="button" :disabled="adminWorkspace.busy[adminWorkspace.active]" :aria-label="`Tutup tab ${tab.label}`" :title="`Tutup ${tab.label}`" class="mr-1 flex h-6 w-6 items-center justify-center rounded text-muted hover:bg-page" @click="close(tab)"><svg aria-hidden="true" width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m4 4 8 8m0-8-8 8" /></svg></button>
        </div>
    </nav>
</template>
