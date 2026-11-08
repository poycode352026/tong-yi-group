<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { setWorkspaceBusy, isWorkspaceReload, adminWorkspace, isAdminDestination, learningDraft, storeLearningDraft, clearLearningDraft, copyLearningData, setWorkspaceDirty } from '../../../utils/learningWorkspace';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import Field from '../../../components/ProgramField.vue';
import SessionMaterialUpload from '../../../components/SessionMaterialUpload.vue';
import LearningLinks from '../../../components/LearningLinks.vue';
import RupiahField from '../../../components/RupiahField.vue';

const abilities = usePage().props.auth?.abilities ?? {};
const props = defineProps({ program: Object, editorId: Number, learningCatalog: { type: Array, default: () => [] } });
const content = ref(JSON.parse(JSON.stringify(props.program?.content ?? { name: '', category: '', description: '', subprograms: [] })));
const programId = ref(props.program?.id ?? null);
const revision = ref(props.program?.revision ?? 0);
const localCatalog = ref([...props.learningCatalog]);
const fileUploading = ref(false);
let catalogSequence = 0;
async function refreshCatalog() {
    if (fileUploading.value || saving.value) return;
    const sequence = ++catalogSequence;
    try {
        const response = await fetch('/admin/learning/catalog', { headers: { Accept: 'application/json' }, cache: 'no-store' });
        if (!response.ok) return;
        const rows = await response.json();
        if (sequence === catalogSequence && !fileUploading.value && !saving.value) localCatalog.value = rows;
    } catch { /* Pertahankan katalog yang sudah dimuat saat koneksi terputus. */ }
}
watch(fileUploading, () => { catalogSequence++; });
const refreshVisibleCatalog = () => { if (document.visibilityState === 'visible') refreshCatalog(); };
onMounted(() => { refreshCatalog(); window.addEventListener('focus', refreshCatalog); document.addEventListener('visibilitychange', refreshVisibleCatalog); });
onBeforeUnmount(() => { catalogSequence++; window.removeEventListener('focus', refreshCatalog); document.removeEventListener('visibilitychange', refreshVisibleCatalog); });
watch(() => props.learningCatalog, (rows) => { catalogSequence++; localCatalog.value = [...rows]; });

const publishedAt = ref(props.program?.published_at ?? null);
const baseline = ref(JSON.stringify(content.value));
const savedIds = computed(() => {
    const ids = new Set();
    const walk = (value) => { if (!value || typeof value !== 'object') return; if (value.id) ids.add(value.id); Object.values(value).forEach(walk); };
    walk(JSON.parse(baseline.value));
    return ids;
});
function discardNew(items, index) {
    if (!savedIds.value.has(items[index].id) && window.confirm('Batalkan bagian baru ini beserta isian di dalamnya?')) {
        items.splice(index, 1);
        if (!content.value.subprograms.length) activeSection.value = 'identity';
    }
}
function cancelNewSubprogram() {
    const sub = activeSub.value;
    if (!sub || savedIds.value.has(sub.id) || saving.value || fileUploading.value) return;
    const hasInput = [sub.name, sub.audience, sub.variant].some((value) => value?.trim()) || sub.levels.length > 0;
    if (hasInput && !window.confirm('Hapus subprogram baru beserta isian yang belum disimpan di dalamnya?')) return;
    const index = subIndex.value;
    content.value.subprograms.splice(index, 1);
    const remaining = content.value.subprograms[Math.min(index, content.value.subprograms.length - 1)];
    selectedSubId.value = remaining?.id ?? null;
    selectedLevelId.value = null;
    selectedPackageId.value = null;
    selectedSessionId.value = null;
    activeSection.value = remaining ? 'subprograms' : 'identity';
    errors.value = {};
    failure.value = '';
    message.value = 'Subprogram baru dibatalkan.';
    persistLocal();
    suspendProgram();
}
function removeLevel() {
    const sub = activeSub.value;
    const level = activeLevel.value;
    if (!sub || !level || !window.confirm(`Hapus level “${level.name || 'Tanpa nama'}” dari daftar aktif? Level dan materinya tetap tersedia dalam arsip Sistem untuk Superadmin.`)) return;
    sub.archived_levels ??= [];
    sub.archived_levels.push(sub.levels.splice(levelIndex.value, 1)[0]);
    selectedLevelId.value = sub.levels[0]?.id ?? null;
    selectedPackageId.value = null;
    selectedSessionId.value = null;
    errors.value = {};
    failure.value = '';
}
const dirty = computed(() => JSON.stringify(content.value) !== baseline.value);
watch(dirty, (value) => setWorkspaceDirty('programs', value), { immediate: true });
const saving = ref(false);
watch([saving, fileUploading], ([savingNow, uploading]) => setWorkspaceBusy('programs', savingNow || uploading), { immediate: true });
const errors = ref({});
const message = ref('');
const failure = ref('');
const errorPanel = ref(null);
const id = () => crypto.randomUUID();
const blankSession = () => ({ id: id(), title: '', objectives: '', material: '', exercise: '', reference: '' });
const blankPackage = () => ({ id: id(), theme: '', sessions: Array.from({ length: 8 }, blankSession) });
const addSubprogram = () => {
    const sub = { id: id(), name: '', audience: '', variant: '', levels: [] };
    content.value.subprograms.push(sub);
    activeSection.value = 'subprograms';
    selectedSubId.value = sub.id;
};
const addLevel = (sub) => {
    const level = { id: id(), name: '', package_count: 1, services: [], packages: [blankPackage()] };
    sub.levels.push(level);
    selectedLevelId.value = level.id;
    levelSection.value = 'services';
};
const addService = (level) => level.services.push({ id: id(), name: '', delivery: '', price: '', minimum: '', maximum: '' });
const changePackages = (level, value) => {
    level.package_count = value;
    const count = Number(value);
    if (!Number.isInteger(count) || count < 1 || count > 100) return;
    level.package_count = count;
    while (level.packages.length < count) level.packages.push(blankPackage());
};
const activeSection = ref('identity');
const subDetails = ref(null);
const selectedSubId = ref(null);
const selectedLevelId = ref(null);
const levelSection = ref('services');
const selectedPackageId = ref(null);
const selectedSessionId = ref(null);
const subIndex = computed(() => Math.max(0, content.value.subprograms.findIndex((sub) => sub.id === selectedSubId.value)));
const activeSub = computed(() => content.value.subprograms[subIndex.value]);
const levelIndex = computed(() => Math.max(0, activeSub.value?.levels.findIndex((level) => level.id === selectedLevelId.value) ?? 0));
const activeLevel = computed(() => activeSub.value?.levels[levelIndex.value]);
const packageIndex = computed(() => Math.max(0, activeLevel.value?.packages.findIndex((pack) => pack.id === selectedPackageId.value) ?? 0));
const activePackage = computed(() => activeLevel.value?.packages[packageIndex.value]);
const sessionIndex = computed(() => Math.max(0, activePackage.value?.sessions.findIndex((session) => session.id === selectedSessionId.value) ?? 0));
const activeSession = computed(() => activePackage.value?.sessions[sessionIndex.value]);
const subPath = computed(() => `content.subprograms.${subIndex.value}`);
const levelPath = computed(() => `${subPath.value}.levels.${levelIndex.value}`);
const packagePath = computed(() => `${levelPath.value}.packages.${packageIndex.value}`);
const sessionPath = computed(() => `${packagePath.value}.sessions.${sessionIndex.value}`);
const levelCount = computed(() => content.value.subprograms.reduce((total, sub) => total + sub.levels.length, 0));
const sessionFields = [
    { key: 'title', label: 'Judul materi', type: 'text' },
    { key: 'objectives', label: 'Tujuan belajar', type: 'textarea' },
    { key: 'material', label: 'Materi wajib', type: 'textarea' },
    { key: 'exercise', label: 'Panduan latihan untuk guru', type: 'textarea', hint: 'Arahan dan referensi bagi guru untuk menyiapkan latihan. Ujian Tong Yi diatur terpisah pada paket atau akhir level.' },
];
function selectSub(sub) {
    if (activeSub.value?.id !== sub.id) {
        selectedLevelId.value = null;
        selectedPackageId.value = null;
        selectedSessionId.value = null;
    }
    selectedSubId.value = sub.id;
    activeSection.value = 'subprograms';
}
function errorLabel(path) {
    const parts = path.split('.');
    const resourceLabel = parts.includes('frameworks') ? 'Acuan pembelajaran' : parts.includes('material_versions') ? 'Bahan referensi' : parts.includes('assessment_versions') ? 'Ujian' : null;
    const label = resourceLabel ?? { module_title: 'Nama modul', module_description: 'Ringkasan modul', module_section: 'Bagian modul / halaman PDF', name: 'Nama', category: 'Kategori', description: 'Pengenalan', audience: 'Kelompok peserta', variant: 'Variasi belajar', package_count: 'Jumlah paket', price: 'Harga', minimum: 'Minimum peserta', maximum: 'Maksimum peserta', delivery: 'Metode', theme: 'Tema paket', title: 'Judul materi', objectives: 'Tujuan belajar', material: 'Materi wajib', exercise: 'Panduan latihan untuk guru', reference: 'Referensi' }[parts.at(-1)] ?? 'Data program';
    const context = [];
    for (const [key, title] of [['subprograms', 'Subprogram'], ['levels', 'Level'], ['archived_levels', 'Level arsip'], ['services', 'Layanan'], ['packages', 'Paket'], ['sessions', 'Sesi']]) {
        const i = parts.indexOf(key);
        if (i >= 0 && /^\d+$/.test(parts[i + 1])) context.push(`${title} ${Number(parts[i + 1]) + 1}`);
    }
    return [...context, label].join(' · ');
}
async function revealError(path) {
    const parts = path.split('.');
    if (parts.includes('subprograms')) {
        const sub = content.value.subprograms[Number(parts[2])];
        if (sub) {
            selectSub(sub);
            if (parts.includes('archived_levels')) {
                message.value = 'Ada isian tidak valid pada level arsip. Superadmin dapat mengembalikannya melalui Sistem → Arsip Level untuk diperbaiki.';
                await nextTick();
                const archive = document.getElementById('archived-levels');
                archive?.scrollIntoView({ block: 'center' });
                archive?.focus({ preventScroll: true });
                return;
            }
            const level = sub.levels[Number(parts[4])];
            if (level) {
                selectedLevelId.value = level.id;
                levelSection.value = parts.includes('assessment_versions') ? 'assessments' : parts.includes('packages') || parts.some((part) => ['material_versions', 'module_title', 'module_description'].includes(part)) ? 'curriculum' : parts.includes('frameworks') ? 'references' : 'services';
                if (parts.includes('packages')) {
                    const pack = level.packages[Number(parts[6])];
                    selectedPackageId.value = pack?.id;
                    selectedSessionId.value = pack?.sessions[Number(parts[8])]?.id;
                }
            }
        }
    } else activeSection.value = 'identity';
    const resourceField = ['frameworks', 'material_versions', 'assessment_versions'].find((field) => parts.includes(field));
    if (resourceField) {
        await nextTick();
        const node = parts.includes('sessions') ? activeSession.value : parts.includes('packages') ? activePackage.value : activeLevel.value;
        document.getElementById(`learning-${node?.id}-${resourceField}`)?.focus();
        return;
    }
    await nextTick();
    if (parts.includes('subprograms') && !parts.includes('levels') && subDetails.value) subDetails.value.open = true;
    const target = document.getElementById(path);
    target?.scrollIntoView({ block: 'center', behavior: 'smooth' });
    target?.focus({ preventScroll: true });
}
const beforeUnload = (event) => { suspendProgram(); if (!isWorkspaceReload() && dirty.value) { event.preventDefault(); event.returnValue = ''; } };
let removeGuard = () => {};
onMounted(() => {
    window.addEventListener('beforeunload', beforeUnload);
    window.addEventListener('tong-yi:workspace-suspend', suspendProgram);
    removeGuard = router.on('before', (event) => {
        if (saving.value || fileUploading.value) return false;
        if (adminWorkspace.closing === 'programs') { sessionStorage.removeItem(recoveryKey); return; }
        if (event.detail.visit.method.toLowerCase() === 'get' && isAdminDestination(event.detail.visit.url)) { suspendProgram(); return; }
        if (dirty.value && !window.confirm('Ada isian belum tersimpan. Tetap tinggalkan halaman?')) return false;
        suspendProgram();
    });
});
onBeforeUnmount(() => { if (typeof window !== 'undefined') window.removeEventListener('beforeunload', beforeUnload); window.removeEventListener('tong-yi:workspace-suspend', suspendProgram); removeGuard(); });
const clientId = ref(null);
const recovery = ref(null);
const programBufferKey = `programs:${props.program?.id ?? 'new'}`;
const recoveryKey = `tong-yi:program:${props.editorId}:${props.program?.id ?? 'new'}`;
function suspendProgram() {
    const key = `programs:${programId.value ?? 'new'}`;
    if (!dirty.value) return;
    storeLearningDraft(key, { content: copyLearningData(content.value), baseline: baseline.value, programId: programId.value, revision: revision.value, clientId: clientId.value, recoveryKey, errors: copyLearningData(errors.value), failure: failure.value,
        view: { activeSection: activeSection.value, selectedSubId: selectedSubId.value, selectedLevelId: selectedLevelId.value, levelSection: levelSection.value, selectedPackageId: selectedPackageId.value, selectedSessionId: selectedSessionId.value } });
}
function persistLocal() {
    try {
        if (!dirty.value) { sessionStorage.removeItem(recoveryKey); return; }
        sessionStorage.setItem(recoveryKey, JSON.stringify({ content: content.value, baseline: baseline.value, programId: programId.value, revision: revision.value, clientId: clientId.value }));
    } catch { /* Pemulihan lokal opsional; simpan ke server tetap melalui tindakan eksplisit. */ }
}
function restoreLocal() {
    if (!recovery.value) return;
    const saved = recovery.value;
    content.value = saved.content;
    baseline.value = saved.baseline;
    programId.value = saved.programId;
    revision.value = saved.revision;
    clientId.value = saved.clientId;
    recovery.value = null;
    message.value = 'Isian dipulihkan dari tab ini. Terbitkan untuk menyimpannya ke server. Jika versi server berubah, penyimpanan akan ditolak agar data tidak tertimpa.';
}
function discardLocal() {
    if (!window.confirm('Abaikan salinan isian yang belum tersimpan dari tab ini?')) return;
    recovery.value = null;
    sessionStorage.removeItem(recoveryKey);
}
onMounted(() => {
    clientId.value = crypto.randomUUID();
    try {
        const saved = JSON.parse(sessionStorage.getItem(recoveryKey));
        if (saved?.content && typeof saved.baseline === 'string' && saved.clientId) recovery.value = saved;
    } catch { /* Salinan lokal tidak tersedia. */ }
    const buffered = learningDraft(programBufferKey);
    if (buffered) {
        recovery.value = buffered;
        restoreLocal();
        errors.value = buffered.errors;
        failure.value = buffered.failure;
        activeSection.value = buffered.view.activeSection;
        selectedSubId.value = buffered.view.selectedSubId;
        selectedLevelId.value = buffered.view.selectedLevelId;
        levelSection.value = buffered.view.levelSection;
        selectedPackageId.value = buffered.view.selectedPackageId;
        selectedSessionId.value = buffered.view.selectedSessionId;
    }
    watch(content, () => { persistLocal(); if (dirty.value) suspendProgram(); else clearLearningDraft(`programs:${programId.value ?? 'new'}`); }, { deep: true, flush: 'sync' });
});

async function save(action = 'draft', leave = false) {
    if (saving.value || fileUploading.value) return;
    saving.value = true;
    errors.value = {};
    message.value = '';
    failure.value = '';
    // Bekukan payload supaya indikator kotor tetap benar bila pengguna mengetik saat koneksi berjalan.
    const snapshot = JSON.stringify(content.value);
    const wasNew = !programId.value;
    try {
        const token = document.cookie.split('; ').find((item) => item.startsWith('XSRF-TOKEN='))?.split('=').slice(1).join('=');
        const response = await fetch(programId.value ? `/admin/programs/${programId.value}` : '/admin/programs', {
            method: programId.value ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-XSRF-TOKEN': decodeURIComponent(token ?? '') },
            body: JSON.stringify({ client_id: clientId.value, action, revision: revision.value, content: JSON.parse(snapshot) }),
        });
        if (!response.ok) {
            const result = await response.json().catch(() => ({}));
            if (response.status === 422) errors.value = Object.fromEntries(Object.entries(result.errors ?? {}).map(([key, values]) => [key, values[0]]));
            failure.value = response.status === 409 ? result.message : response.status === 419 || response.status === 401 ? 'Sesi berakhir. Salin isian penting sebelum masuk kembali; data yang belum tersimpan tetap ada di halaman ini.' : response.status === 403 ? 'Akun tidak memiliki izin menyimpan program. Isian Anda tetap ada di halaman ini.' : 'Program belum tersimpan. Periksa isian atau coba kembali.';
            await nextTick(); errorPanel.value?.focus();
            return;
        }
        const result = await response.json();
        clearLearningDraft(programBufferKey);
        clearLearningDraft(`programs:${programId.value ?? 'new'}`);
        programId.value = result.id;
        revision.value = result.revision;
        publishedAt.value = result.published_at;
        baseline.value = snapshot;
        persistLocal();
        message.value = action === 'publish' ? 'Program berhasil diterbitkan di katalog Admin.' : 'Draf berhasil disimpan di server dan dapat dilanjutkan dari daftar program.';
        if (!dirty.value && (leave || wasNew)) {
            saving.value = false;
            router.visit(leave ? '/admin/programs' : `/admin/programs/${result.id}/edit`, { replace: wasNew, preserveScroll: true });
        }
    } catch {
        failure.value = 'Koneksi gagal. Isian tetap ada di halaman ini. Coba simpan kembali.';
        await nextTick(); errorPanel.value?.focus();
    } finally {
        saving.value = false;
    }
}
function back() {
    router.visit('/admin/programs');
}
</script>

<template>
    <Head :title="programId ? 'Ubah Program' : 'Tambah Program'" />
    <AdminLayout>
        <button type="button" class="text-xs font-medium text-muted hover:text-brand" :disabled="saving" @click="back">Kembali ke daftar program</button>
        <div class="editor-heading mt-5 flex flex-wrap items-center justify-between gap-4">
            <div><h1 class="text-2xl font-semibold tracking-tight text-brand sm:text-3xl">{{ programId ? 'Ubah program' : 'Tambah program' }}</h1><p class="mt-2 text-sm leading-6 text-muted">Atur materi setiap sesi, referensi guru, dan ujian Tong Yi.</p></div>
            <span class="rounded-full border border-line bg-white px-3 py-1.5 text-xs font-medium text-muted">{{ publishedAt ? 'Versi terbit tersedia' : 'Draf program' }}</span>
        </div>
        <div role="region" aria-label="Tindakan program" class="editor-actions sticky top-[76px] z-20 mt-6 flex flex-wrap items-center justify-between gap-4 border-y border-line bg-page/95 py-4 backdrop-blur-md">
            <div class="text-xs leading-5 text-muted"><p aria-live="polite" class="font-medium">{{ saving ? 'Sedang menyimpan…' : dirty ? 'Ada perubahan belum tersimpan' : programId ? 'Semua perubahan tersimpan' : 'Draf belum disimpan' }}</p><p class="mt-1 text-[11px]">Draf dan versi terbit disimpan terpisah.</p></div>
            <div class="flex flex-wrap gap-3"><button v-if="abilities['programs.publish']" type="button" class="admin-button" :disabled="saving || fileUploading || !!recovery" @click="save('publish')">{{ publishedAt ? 'Terbitkan perubahan' : 'Terbitkan program' }}</button></div>
        </div>
        <div v-if="failure" ref="errorPanel" tabindex="-1" role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 p-5 text-red-800">
            <p class="text-sm font-semibold">{{ failure }}</p>
            <ul v-if="Object.keys(errors).length" class="mt-3 space-y-2 text-sm"><li v-for="(error, path) in errors" :key="path"><button type="button" class="text-left underline decoration-red-300 underline-offset-4" @click="revealError(path)">{{ errorLabel(path) }}: {{ error }}</button></li></ul>
        </div>
        <div v-if="recovery" role="status" class="mt-6 rounded-xl border border-line bg-accent-soft p-5"><p class="text-sm leading-6">Ada isian dari tab ini yang belum tersimpan. Pulihkan untuk melanjutkan pekerjaan Anda.</p><div class="mt-3 flex gap-3"><button type="button" class="admin-secondary" @click="restoreLocal">Pulihkan isian</button><button type="button" class="admin-secondary" @click="discardLocal">Abaikan salinan</button></div></div>
        <p v-if="message" role="status" class="mt-6 rounded-xl bg-brand-soft px-5 py-4 text-sm text-brand">{{ message }}</p>

        <form id="program-editor-form" class="program-editor mt-6" @submit.prevent>
            <fieldset :disabled="!!recovery || fileUploading" class="min-w-0">
                <div class="editor-workspace grid items-start gap-6 xl:grid-cols-[220px_minmax(0,1fr)]">
                    <div class="rounded-xl border border-line bg-white p-4 xl:hidden"><label for="editor-part" class="field-label">Bagian yang dikerjakan</label><div class="flex gap-2"><select id="editor-part" class="admin-input min-w-0" :value="activeSection === 'identity' ? 'identity' : activeSub?.id" @change="$event.target.value === 'identity' ? activeSection = 'identity' : selectSub(content.subprograms.find((sub) => sub.id === $event.target.value))"><option value="identity">Identitas program</option><option v-for="(sub, index) in content.subprograms" :key="sub.id" :value="sub.id">{{ sub.name || `Subprogram ${index + 1}` }}</option></select><button type="button" class="admin-secondary shrink-0" aria-label="Tambah subprogram" @click="addSubprogram">Tambah</button></div></div>
                    <aside class="editor-outline hidden rounded-xl border border-line bg-white p-3 xl:sticky xl:top-44 xl:block">
                        <p class="px-3 pb-3 pt-2 text-[10px] font-semibold tracking-[0.15em] text-muted">PENGATURAN PROGRAM</p>
                        <button type="button" :class="['outline-item', activeSection === 'identity' && 'is-active']" :aria-pressed="activeSection === 'identity'" @click="activeSection = 'identity'"><span>Identitas program</span><small class="mt-1 block break-words font-normal opacity-75">{{ content.name?.trim() || 'Nama belum diisi' }}</small></button>
                        <div class="my-3 border-t border-line"></div>
                        <div class="flex items-center justify-between px-3 pb-2"><p class="text-[10px] font-semibold tracking-[0.15em] text-muted">SUBPROGRAM</p><span class="text-xs text-muted">{{ content.subprograms.length }}</span></div>
                        <button v-for="(sub, index) in content.subprograms" :key="sub.id" type="button" :class="['outline-item', activeSection === 'subprograms' && activeSub?.id === sub.id && 'is-active']" :aria-pressed="activeSection === 'subprograms' && activeSub?.id === sub.id" @click="selectSub(sub)"><span class="truncate">{{ sub.name || `Subprogram ${index + 1}` }}</span><small class="mt-1 block truncate font-normal opacity-75">{{ [sub.audience, sub.variant].filter(Boolean).join(' · ') || 'Belum dilengkapi' }}</small></button>
                        <button type="button" class="mt-2 w-full rounded-lg border border-dashed border-line px-3 py-2.5 text-left text-xs font-semibold text-interactive hover:bg-page" @click="addSubprogram">Tambah subprogram</button>
                        <p class="px-3 pb-2 pt-5 text-xs leading-5 text-muted">{{ levelCount }} level dalam program ini.<br />Semua bagian disimpan bersama.</p>
                    </aside>

                    <div class="min-w-0">
                        <section v-if="activeSection === 'identity'" key="identity" class="editor-panel">
                            <div class="panel-heading"><div><h2>Identitas program</h2><p>Informasi utama untuk mengenalkan program Anda.</p></div></div>
                            <div class="panel-body space-y-6">
                                <div class="grid gap-5 sm:grid-cols-2"><Field v-model="content.name" label="Nama program" name="content.name" :error="errors['content.name']" placeholder="Contoh: Mandarin" /><Field v-model="content.category" label="Kategori" name="content.category" :error="errors['content.category']" placeholder="Contoh: Bahasa" /></div>
                                <Field v-model="content.description" label="Pengenalan program" name="content.description" type="textarea" :rows="5" :error="errors['content.description']" placeholder="Jelaskan tujuan dan gambaran pembelajaran program ini." />
                                <div class="rounded-lg bg-page p-4 text-xs leading-6 text-muted">Program dapat berasal dari berbagai bidang. Tambahkan subprogram untuk membedakan kelompok peserta atau variasi pembelajaran, misalnya Mandarin Dewasa Sederhana.</div>
                                <div class="flex flex-wrap items-center justify-between gap-4 border-t border-line pt-5"><p class="text-sm text-muted">{{ content.subprograms.length ? `${content.subprograms.length} subprogram telah ditambahkan` : 'Lanjutkan dengan menyusun subprogram.' }}</p><button type="button" class="admin-secondary" @click="content.subprograms.length ? selectSub(content.subprograms[0]) : addSubprogram()">{{ content.subprograms.length ? 'Atur subprogram' : 'Tambah subprogram' }}</button></div>
                            </div>
                        </section>

                        <div v-else-if="activeSub" :key="activeSub.id" class="min-w-0 space-y-6">
                            <section aria-label="Identitas program induk" class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-line bg-brand-soft/50 px-5 py-4">
                                <dl class="grid min-w-0 flex-1 gap-x-8 gap-y-3 sm:grid-cols-2">
                                    <div class="min-w-0"><dt class="text-[10px] font-semibold tracking-wide text-muted">PROGRAM INDUK</dt><dd class="mt-1 break-words text-sm font-semibold text-brand">{{ content.name?.trim() || 'Nama program belum diisi' }}</dd></div>
                                    <div class="min-w-0"><dt class="text-[10px] font-semibold tracking-wide text-muted">KATEGORI</dt><dd class="mt-1 break-words text-sm text-ink">{{ content.category?.trim() || 'Kategori belum diisi' }}</dd></div>
                                </dl>
                                <button type="button" class="shrink-0 text-xs font-semibold text-interactive" @click="activeSection = 'identity'">Ubah identitas program</button>
                            </section>
                            <details ref="subDetails" :key="`settings-${activeSub.id}`" :open="!savedIds.has(activeSub.id)" class="editor-panel sub-settings">
                                <summary class="panel-heading cursor-pointer list-none"><div class="min-w-0"><p class="section-overline">SUBPROGRAM {{ subIndex + 1 }}</p><h2>{{ activeSub.name || 'Subprogram baru' }}</h2><p>{{ [activeSub.audience, activeSub.variant].filter(Boolean).join(' · ') || 'Lengkapi identitas subprogram' }}</p></div><div class="flex flex-wrap items-center gap-4"><span class="text-xs font-medium text-interactive">Atur identitas</span><button v-if="!savedIds.has(activeSub.id)" type="button" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50" :disabled="saving || fileUploading" @click.stop.prevent="cancelNewSubprogram">Hapus subprogram baru</button></div></summary>
                                <div class="panel-body"><div class="grid gap-5 sm:grid-cols-2"><Field v-model="activeSub.name" label="Nama subprogram" :name="`${subPath}.name`" :error="errors[`${subPath}.name`]" placeholder="Contoh: Mandarin Dewasa Sederhana" class="sm:col-span-2" /><Field v-model="activeSub.audience" label="Kelompok peserta" :name="`${subPath}.audience`" :error="errors[`${subPath}.audience`]" list="audience-options" placeholder="Pilih atau tulis kelompok peserta" /><Field v-model="activeSub.variant" label="Variasi belajar" :name="`${subPath}.variant`" :error="errors[`${subPath}.variant`]" list="variant-options" placeholder="Opsional, misalnya Mandarin Sederhana" /></div><div class="mt-4 flex flex-wrap items-center justify-between gap-3"><p class="text-xs leading-6 text-muted">Online/Offline dan Group/Private diatur sebagai layanan pada level.</p></div></div>
                            </details>

                            <section class="editor-panel">
                                <div class="panel-heading"><div><h2>Level pembelajaran</h2><p>Atur acuan, layanan, materi sesi, dan ujian untuk setiap level.</p></div><button type="button" class="admin-secondary shrink-0" @click="addLevel(activeSub)">Tambah level</button></div>
                                <div v-if="!activeSub.levels.length" class="px-6 py-14 text-center"><p class="text-sm font-medium text-brand">Belum ada level</p><p class="mt-2 text-sm text-muted">Tambahkan level pertama untuk menyusun layanan dan kurikulum.</p></div>
                                <template v-else-if="activeLevel">
                                    <div class="flex gap-1 overflow-x-auto border-b border-line bg-page/50 px-5 pt-3" role="group" aria-label="Pilih level"><button v-for="(level, index) in activeSub.levels" :key="level.id" type="button" :aria-pressed="activeLevel.id === level.id" :class="['level-pill', activeLevel.id === level.id && 'is-active']" @click="selectedLevelId = level.id">{{ level.name || `Level ${index + 1}` }}</button></div>
                                    <div class="panel-body space-y-6">
                                        <div class="flex flex-wrap items-center justify-between gap-3"><h3 class="text-sm font-semibold text-brand">{{ activeLevel.name || `Level ${levelIndex + 1}` }}</h3><button type="button" class="rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50" :disabled="saving" @click="removeLevel">Hapus level</button></div>
                                        <div class="grid gap-5 sm:grid-cols-[minmax(0,1fr)_180px]"><Field v-model="activeLevel.name" label="Nama level" :name="`${levelPath}.name`" :error="errors[`${levelPath}.name`]" placeholder="Contoh: Beginner" /><Field :model-value="activeLevel.package_count" label="Jumlah paket aktif" type="number" :min="1" :max="100" :name="`${levelPath}.package_count`" :error="errors[`${levelPath}.package_count`]" @update:model-value="changePackages(activeLevel, $event)" /></div>
                                        <div class="flex flex-wrap items-center justify-between gap-3"><p class="text-xs text-muted">{{ Number(activeLevel.package_count) > 0 ? Number(activeLevel.package_count) * 8 : '—' }} sesi rencana <span class="mx-2 text-line">|</span> 8 sesi per paket</p></div>
                                        <div class="flex flex-wrap gap-6 border-b border-line" role="group" aria-label="Pengaturan level"><button type="button" :aria-pressed="levelSection === 'references'" :class="['section-tab', levelSection === 'references' && 'is-active']" @click="levelSection = 'references'">Modul</button><button type="button" :aria-pressed="levelSection === 'services'" :class="['section-tab', levelSection === 'services' && 'is-active']" @click="levelSection = 'services'">Layanan <span class="ml-1 text-xs opacity-60">{{ activeLevel.services.length }}</span></button><button type="button" :aria-pressed="levelSection === 'curriculum'" :class="['section-tab', levelSection === 'curriculum' && 'is-active']" @click="levelSection = 'curriculum'">Materi sesi</button><button type="button" :aria-pressed="levelSection === 'assessments'" :class="['section-tab', levelSection === 'assessments' && 'is-active']" @click="levelSection = 'assessments'">Ujian</button></div>

                                        <LearningLinks :placement="{ program_id: programId || clientId, subprogram_id: activeSub.id, level_id: activeLevel.id, level_name: activeLevel.name, program_revision: revision, saved: !!programId && savedIds.has(activeLevel.id) }" v-if="levelSection === 'references'" :key="`references-${activeLevel.id}`" :node="activeLevel" :catalog="localCatalog" :sections="['frameworks', 'materials']" scope="level" :name="`level ${activeLevel.name || levelIndex + 1}`" />
                                        <div v-else-if="levelSection === 'assessments'" class="space-y-5"><LearningLinks :placement="{ program_id: programId || clientId, subprogram_id: activeSub.id, level_id: activeLevel.id, level_name: activeLevel.name, program_revision: revision, saved: !!programId && savedIds.has(activeLevel.id) }" :node="activeLevel" :catalog="localCatalog" scope="level" :sections="['assessments']" :name="`level ${activeLevel.name || levelIndex + 1}`" /><template v-if="activePackage"><div><label for="exercise-package-choice" class="field-label">Paket belajar</label><select id="exercise-package-choice" :value="activePackage.id" @change="selectedPackageId = $event.target.value; selectedSessionId = null" class="admin-input max-w-sm"><option v-for="(pack, index) in activeLevel.packages" :key="pack.id" :value="pack.id">Paket {{ index + 1 }}{{ index >= Number(activeLevel.package_count) ? ' (nonaktif)' : '' }}</option></select></div><LearningLinks :placement="{ program_id: programId || clientId, subprogram_id: activeSub.id, level_id: activeLevel.id, level_name: activeLevel.name, program_revision: revision, saved: !!programId && savedIds.has(activeLevel.id) }" :key="`exercise-${activePackage.id}`" :node="activePackage" :catalog="localCatalog" scope="package" :sections="['assessments']" :name="`paket ${packageIndex + 1}`" /></template></div>
                                        <div v-else-if="levelSection === 'services'" key="services" class="space-y-5">
                                            <div class="flex flex-wrap items-center justify-between gap-4"><p class="max-w-sm text-xs leading-6 text-muted">Tambahkan hanya layanan yang ditawarkan. Setiap layanan memiliki harga dan kapasitas sendiri.</p><button type="button" class="admin-secondary" @click="addService(activeLevel)">Tambah layanan</button></div>
                                            <div v-if="!activeLevel.services.length" class="rounded-lg border border-dashed border-line px-5 py-10 text-center text-sm text-muted">Belum ada layanan. Tambahkan sesuai kebutuhan level ini.</div>
                                            <div v-for="(service, index) in activeLevel.services" :key="service.id" class="service-editor">
                                                <div class="mb-5 flex items-center justify-between gap-3"><h3 class="text-sm font-semibold text-brand">{{ service.name || `Layanan ${index + 1}` }}{{ service.delivery ? ` ${service.delivery === 'online' ? 'Online' : 'Offline'}` : '' }}</h3><button v-if="!savedIds.has(service.id)" type="button" class="quiet-action" @click="discardNew(activeLevel.services, index)">Batalkan</button></div>
                                                <div class="grid gap-5 sm:grid-cols-2"><Field v-model="service.name" label="Jenis layanan" :name="`${levelPath}.services.${index}.name`" :error="errors[`${levelPath}.services.${index}.name`]" list="service-options" placeholder="Group atau Private" /><div><label :for="`${levelPath}.services.${index}.delivery`" class="field-label">Metode belajar</label><select :id="`${levelPath}.services.${index}.delivery`" v-model="service.delivery" class="admin-input" :aria-invalid="!!errors[`${levelPath}.services.${index}.delivery`]" :aria-describedby="errors[`${levelPath}.services.${index}.delivery`] ? `${levelPath}.services.${index}.delivery-error` : undefined"><option value="">Pilih metode</option><option value="online">Online</option><option value="offline">Offline</option></select><p v-if="errors[`${levelPath}.services.${index}.delivery`]" :id="`${levelPath}.services.${index}.delivery-error`" class="field-error">{{ errors[`${levelPath}.services.${index}.delivery`] }}</p></div></div>
                                                <div class="mt-5 grid gap-5 border-t border-line pt-5 sm:grid-cols-3"><RupiahField v-model="service.price" label="Harga (Rp)" :name="`${levelPath}.services.${index}.price`" :error="errors[`${levelPath}.services.${index}.price`]" placeholder="Belum ditetapkan" /><Field v-model="service.minimum" label="Minimum peserta" type="number" :min="1" :name="`${levelPath}.services.${index}.minimum`" :error="errors[`${levelPath}.services.${index}.minimum`]" /><Field v-model="service.maximum" label="Maksimum peserta" type="number" :min="1" :name="`${levelPath}.services.${index}.maximum`" :error="errors[`${levelPath}.services.${index}.maximum`]" /></div>
                                            </div>
                                        </div>

                                        <div v-else-if="activePackage" key="curriculum" class="space-y-5">
                                            <p v-if="activeLevel.packages.length > Number(activeLevel.package_count)" role="status" class="rounded-lg bg-accent-soft px-4 py-3 text-xs leading-6">Paket di luar rencana aktif tetap disimpan. Pilih paket tersebut untuk melihat atau melengkapi materinya.</p>
                                            <div class="grid gap-5 sm:grid-cols-[180px_minmax(0,1fr)]"><div><label for="package-choice" class="field-label">Paket belajar</label><select id="package-choice" :value="activePackage.id" class="admin-input" @change="selectedPackageId = $event.target.value; selectedSessionId = null"><option v-for="(pack, index) in activeLevel.packages" :key="pack.id" :value="pack.id">Paket {{ index + 1 }}{{ index >= Number(activeLevel.package_count) ? ' (nonaktif)' : '' }}</option></select></div><Field v-model="activePackage.theme" label="Tema paket" :name="`${packagePath}.theme`" :error="errors[`${packagePath}.theme`]" placeholder="Tema atau fokus pembelajaran" /></div>
                                            <div class="grid gap-5 border-t border-line pt-5 md:grid-cols-[140px_minmax(0,1fr)]">
                                                <div class="flex gap-1 overflow-x-auto md:block" role="group" aria-label="Pilih sesi"><button v-for="(session, index) in activePackage.sessions" :key="session.id" type="button" :aria-pressed="activeSession?.id === session.id" :class="['session-choice', activeSession?.id === session.id && 'is-active']" @click="selectedSessionId = session.id"><span class="whitespace-nowrap">Sesi {{ index + 1 }}</span><span class="hidden truncate text-[10px] font-normal opacity-70 md:block">{{ session.title || 'Belum diisi' }}</span></button></div>
                                                <div v-if="activeSession" :key="activeSession.id" class="min-w-0 space-y-5"><div class="pb-1"><p class="section-overline">PAKET {{ packageIndex + 1 }}</p><h3 class="mt-1 text-base font-semibold text-brand">Materi sesi {{ sessionIndex + 1 }}</h3></div><Field v-for="field in sessionFields" :key="field.key" v-model="activeSession[field.key]" :label="field.label" :hint="field.hint" :type="field.type" :rows="field.key === 'material' ? 5 : 3" :name="`${sessionPath}.${field.key}`" :error="errors[`${sessionPath}.${field.key}`]" /><SessionMaterialUpload :catalog="localCatalog" :node="activeSession" :name="`sesi ${sessionIndex + 1}`" @busy="fileUploading = $event" @uploaded="localCatalog.push($event)" /><details v-if="activeSession.module_section" class="text-xs text-muted"><summary class="cursor-pointer">Catatan bagian modul lama</summary><p class="mt-2">{{ activeSession.module_section }}</p></details><details v-if="activeSession.reference?.trim()" class="text-xs text-muted"><summary class="cursor-pointer">Catatan referensi lama</summary><p class="mt-2 whitespace-pre-wrap leading-6">{{ activeSession.reference }}</p></details></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </section>

                        </div>
                        <div v-else class="editor-panel p-10 text-center text-sm text-muted">Pilih atau tambahkan subprogram untuk melanjutkan.</div>
                    </div>
                </div>
                <datalist id="audience-options"><option>Anak-anak</option><option>Dewasa</option></datalist><datalist id="variant-options"><option>Mandarin Tradisional</option><option>Mandarin Sederhana</option></datalist><datalist id="service-options"><option>Group</option><option>Private</option></datalist>

            </fieldset>
        </form>
    </AdminLayout>
</template>
