import { reactive, watch } from 'vue';
import { isWorkspaceDestination } from './workspaceNavigation.js';

const storageKey = 'tong-yi-admin-workspace:v2';
const state = reactive({ owner: null, drafts: {} });
let hydrating = false;
let internalReload = null;
export function isWorkspaceReload() { return internalReload && !internalReload.event.defaultPrevented && Date.now() < internalReload.until; }
function storedValue(value) {
    if (typeof File !== 'undefined' && value instanceof File) return { __fileToReselect: value.name };
    if (Array.isArray(value)) return value.map(storedValue);
    if (value && typeof value === 'object') return Object.fromEntries(Object.entries(value).map(([key, item]) => [key, storedValue(item)]));
    return value;
}
function restoredValue(value, missing) {
    if (value?.__fileToReselect) { missing.push(value.__fileToReselect); return null; }
    if (Array.isArray(value)) return value.map((item) => restoredValue(item, missing));
    if (value && typeof value === 'object') return Object.fromEntries(Object.entries(value).map(([key, item]) => [key, restoredValue(item, missing)]));
    return value;
}
function persist() {
    if (hydrating || !state.owner || typeof sessionStorage === 'undefined') return;
    try { sessionStorage.setItem(storageKey, JSON.stringify({ owner: state.owner, tabs: adminWorkspace.tabs, dirty: adminWorkspace.dirty })); sessionStorage.setItem(`${storageKey}:drafts`, JSON.stringify(storedValue({ owner: state.owner, drafts: state.drafts }))); } catch { /* Kuota browser tidak boleh menghapus buffer yang masih di memori. */ }
}
export function clearLearningWorkspace() {
    hydrating = true;
    state.owner = null; state.drafts = {}; adminWorkspace.tabs = []; adminWorkspace.active = null; adminWorkspace.dirty = {}; adminWorkspace.bases = {}; adminWorkspace.closing = null; adminWorkspace.busy = {};
    try { sessionStorage.removeItem(storageKey); sessionStorage.removeItem(`${storageKey}:drafts`); } catch { /* Penyimpanan opsional. */ }
    hydrating = false;
}
export function ensureLearningWorkspace(user) {
    const owner = user?.email ?? null;
    if (state.owner === owner) return;
    let stored = null;
    try { stored = JSON.parse(sessionStorage.getItem(storageKey)); const drafts = JSON.parse(sessionStorage.getItem(`${storageKey}:drafts`)); if (stored && drafts?.owner === stored.owner) stored.drafts = drafts.drafts; } catch { /* Belum ada salinan. */ }
    clearLearningWorkspace();
    hydrating = true;
    state.owner = owner;
    if (owner && stored?.owner === owner) {
        adminWorkspace.dirty = Object.fromEntries(Object.entries(stored.dirty ?? {}).filter(([key, value]) => typeof value === 'boolean'));
        adminWorkspace.tabs = (stored.tabs ?? []).filter((tab) => typeof tab.key === 'string' && typeof tab.href === 'string' && /^\/admin(?:\/|$)/.test(tab.href) && !tab.href.startsWith('//')).slice(0, 30);
        for (const [key, value] of Object.entries(stored.drafts ?? {})) {
            const missing = [];
            state.drafts[key] = { ...restoredValue(value, missing), missingFiles: missing };
        }
    }
    hydrating = false; persist();
}
export function learningDraft(key) { return state.drafts[key] ?? null; }
export function storeLearningDraft(key, value) { state.drafts[key] = value; persist(); }
export function clearLearningDraft(key) { delete state.drafts[key]; persist(); }
export function hasLearningDraft(kind) { return Object.keys(state.drafts).some((key) => key.startsWith(`${kind}:`)); }
export function copyLearningData(value) {
    if (typeof File !== 'undefined' && value instanceof File) return value;
    if (Array.isArray(value)) return value.map(copyLearningData);
    if (value && typeof value === 'object') return Object.fromEntries(Object.entries(value).map(([key, item]) => [key, copyLearningData(item)]));
    return value;
}
if (typeof window !== 'undefined') {
    document.addEventListener('inertia:location', (event) => {
        if (!isAdminDestination(event.detail.url)) return;
        if (Object.values(adminWorkspace.busy).some(Boolean)) { event.preventDefault(); return; }
        window.dispatchEvent(new Event('tong-yi:workspace-suspend'));
        persist();
        internalReload = { event, until: Date.now() + 1500 };
    });
    window.addEventListener('beforeunload', (event) => {
        if (isWorkspaceReload()) return;
        if (Object.keys(state.drafts).length || Object.values(adminWorkspace.dirty).some(Boolean) || Object.values(adminWorkspace.busy).some(Boolean)) { event.preventDefault(); event.returnValue = ''; }
    });
}

// Satu tab kerja per menu Admin, dengan halaman terakhir di dalam menu tersebut.
export const adminWorkspace = reactive({ tabs: [], active: null, bases: {}, closing: null, dirty: {}, busy: {} });
watch(() => [adminWorkspace.tabs, adminWorkspace.dirty, state.drafts], persist, { deep: true, flush: 'sync' });
export function registerAdminPage(navigation, url, kind = null) {
    if (/^\/admin\/(login|logout)(?:\?|$)/.test(url)) return;
    const allowed = new Set(navigation.map((item) => item.key));
    adminWorkspace.tabs = adminWorkspace.tabs.filter((tab) => allowed.has(tab.key));
    for (const name of Object.keys(state.drafts)) if (!allowed.has(name.split(':')[0])) delete state.drafts[name];
    adminWorkspace.bases = Object.fromEntries(navigation.map((item) => [item.path, item.key]));
    const path = url.split('?')[0];
    const menu = navigation.find((item) => item.path !== '/admin' && (path === item.path || path.startsWith(`${item.path}/`)))
        ?? navigation.find((item) => item.key === kind)
        ?? navigation.find((item) => item.path === path);
    if (!menu) return;
    adminWorkspace.active = menu.key;
    const existing = adminWorkspace.tabs.find((tab) => tab.key === menu.key);
    if (existing) Object.assign(existing, { href: url, label: menu.label });
    else adminWorkspace.tabs.push({ key: menu.key, label: menu.label, href: url });
}
export const isAdminDestination = (url) => isWorkspaceDestination(url, typeof window !== 'undefined' ? window.location.origin : 'http://localhost');
export function workspaceNavigationUrl(path) { return adminWorkspace.tabs.find((tab) => tab.key === adminWorkspace.bases[path])?.href ?? path; }
export function setWorkspaceDirty(key, dirty) { adminWorkspace.dirty[key] = dirty; }
export function workspaceDirty(key) { return !!adminWorkspace.dirty[key] || hasLearningDraft(key); }
// Status terakhir formulir pada tab tetap sama ketika tab aktif/nonaktif.
// Buffer halaman lain tetap dilindungi oleh workspaceDirty saat menutup tab.
export function workspaceTabDirty(key) {
    if (Object.prototype.hasOwnProperty.call(adminWorkspace.dirty, key)) return !!adminWorkspace.dirty[key];
    return workspaceDirty(key);
}
export function dropWorkspaceTab(key) {
    if (adminWorkspace.active === key) adminWorkspace.active = null;
    adminWorkspace.tabs = adminWorkspace.tabs.filter((tab) => tab.key !== key);
    delete adminWorkspace.dirty[key];
    for (const name of Object.keys(state.drafts)) if (name.startsWith(`${key}:`)) {
        const recoveryKey = state.drafts[name]?.recoveryKey;
        if (recoveryKey) { try { sessionStorage.removeItem(recoveryKey); } catch { /* Salinan lokal opsional. */ } }
        delete state.drafts[name];
    }
}

export function setWorkspaceBusy(key, busy) { adminWorkspace.busy[key] = busy; }
