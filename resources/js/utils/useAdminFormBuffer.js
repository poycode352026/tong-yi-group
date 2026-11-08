import { onBeforeUnmount, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { ensureLearningWorkspace, setWorkspaceBusy, adminWorkspace, copyLearningData, learningDraft, storeLearningDraft, clearLearningDraft, setWorkspaceDirty, isAdminDestination } from './learningWorkspace';

export function useAdminFormBuffer(key, form, extras = {}) {
    ensureLearningWorkspace(usePage().props.auth?.user);
    const name = `${key}:form`;
    let defaults = copyLearningData(form.data());
    const cached = learningDraft(name);
    if (cached) {
        defaults = cached.defaults;
        form.defaults(copyLearningData(defaults));
        Object.assign(form, copyLearningData(cached.data));
        form.setError(copyLearningData(cached.errors));
        for (const [field, value] of Object.entries(cached.extras)) if (extras[field]) extras[field].value = copyLearningData(value);
    }
    watch(() => form.processing, (busy) => setWorkspaceBusy(key, busy), { immediate: true });
    watch(() => form.isDirty, (dirty) => setWorkspaceDirty(key, dirty), { immediate: true, flush: 'sync' });
    function suspend() {
        if (form.isDirty || form.hasErrors) storeLearningDraft(name, { data: copyLearningData(form.data()), defaults, errors: copyLearningData(form.errors), extras: Object.fromEntries(Object.entries(extras).map(([field, value]) => [field, copyLearningData(value.value)])) });
        else clearLearningDraft(name);
    }
    window.addEventListener('beforeunload', suspend);
    window.addEventListener('tong-yi:workspace-suspend', suspend);
    const stop = router.on('before', (event) => {
        if (adminWorkspace.closing === key) return;
        if (event.detail.visit.method.toLowerCase() !== 'get') return;
        if (form.processing) { event.preventDefault(); return; }
        if (!isAdminDestination(event.detail.visit.url) && form.isDirty && !confirm('Ada isian belum disimpan. Tinggalkan halaman?')) { event.preventDefault(); return; }
        suspend();
    });
    onBeforeUnmount(() => { stop(); window.removeEventListener('beforeunload', suspend); window.removeEventListener('tong-yi:workspace-suspend', suspend); });
    return {
        saved() { defaults = copyLearningData(form.data()); form.defaults(); clearLearningDraft(name); setWorkspaceDirty(key, false); },
        baseline() { defaults = copyLearningData(form.data()); },
    };
}
