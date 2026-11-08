<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { adminWorkspace, clearLearningWorkspace, ensureLearningWorkspace } from '../utils/learningWorkspace';
import AdminTabs from '../components/AdminTabs.vue';
import AdminNavigation from '../components/AdminNavigation.vue';
import logo from '../../../assets/brand/tong-yi-group-logo.png';

const page = usePage();
const logout = useForm({});
ensureLearningWorkspace(page.props.auth?.user);
function signOut() { logout.post('/admin/logout', { onSuccess: clearLearningWorkspace }); }
const menuOpen = ref(false);
const workspaceReady = ref(false);
const themeKey = 'tong-yi-admin-theme';
function initialTheme() {
    if (typeof window === 'undefined') return 'light';
    try { const saved = localStorage.getItem(themeKey); if (saved === 'dark' || saved === 'light') return saved; } catch { /* Preferensi opsional. */ }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}
const theme = ref(initialTheme());
function toggleTheme() {
    theme.value = theme.value === 'dark' ? 'light' : 'dark';
    try { localStorage.setItem(themeKey, theme.value); } catch { /* Tema tetap bisa diganti tanpa penyimpanan. */ }
}
function syncTheme(event) {
    if (event.key === themeKey) theme.value = initialTheme();
}

const sidebarCollapsed = ref(false);
watch(sidebarCollapsed, (value) => {
    try { localStorage.setItem('tong-yi-admin-sidebar-collapsed', String(value)); } catch { /* Preferensi lokal opsional. */ }
});
const account = computed(() => page.props.auth?.user ?? page.props.admin ?? {});
const initials = computed(() => (account.value.name ?? 'Admin').trim().split(/\s+/).slice(0, 2).map((word) => word[0]).join('').toUpperCase());
const path = computed(() => page.url.split('?')[0]);
const navigation = computed(() => page.props.adminNavigation ?? []);
const title = computed(() => { if (/^\/admin\/system\/activity\/\d+$/.test(path.value)) return 'Detail aktivitas'; const match = navigation.value.find((item) => item.path !== '/admin' && path.value.startsWith(item.path)); return navigation.value.find((item) => item.path === path.value)?.label ?? (match ? `${path.value.endsWith('/create') ? 'Tambah' : 'Ubah'} · ${match.label}` : 'Administrasi'); });
const accountRole = computed(() => account.value.is_superadmin ? 'Superadmin' : 'Administrator');
async function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    await nextTick();
    document.getElementById(sidebarCollapsed.value ? 'admin-sidebar-open' : 'admin-sidebar-close')?.focus();
}
const closeOnEscape = (event) => { if (event.key === 'Escape' && menuOpen.value) { menuOpen.value = false; document.getElementById('admin-menu-toggle')?.focus(); } };
watch(() => page.url, () => { menuOpen.value = false; });
onMounted(() => {
    try { sidebarCollapsed.value = localStorage.getItem('tong-yi-admin-sidebar-collapsed') === 'true'; } catch { /* Gunakan tampilan awal. */ }
    window.addEventListener('keydown', closeOnEscape);
    window.addEventListener('storage', syncTheme);
    workspaceReady.value = true;
});
onBeforeUnmount(() => { if (typeof window !== 'undefined') { window.removeEventListener('keydown', closeOnEscape); window.removeEventListener('storage', syncTheme); } });
</script>

<template>
    <div :data-theme="theme" :inert="!workspaceReady" :aria-busy="!workspaceReady" class="admin-shell min-h-screen bg-page text-ink">
        <a href="#admin-content" class="admin-skip">Lewati navigasi</a>
        <aside v-show="!sidebarCollapsed" id="admin-desktop-sidebar" aria-label="Sidebar Admin" class="fixed inset-y-0 left-0 z-40 hidden w-60 flex-col border-r border-line bg-white lg:flex">
            <div class="relative border-b border-line px-7 pb-6 pt-7"><button id="admin-sidebar-close" type="button" aria-label="Tutup sidebar" title="Tutup sidebar" aria-controls="admin-desktop-sidebar" :aria-expanded="true" class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-lg text-muted hover:bg-page hover:text-brand" @click="toggleSidebar"><svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m6 6 12 12M18 6 6 18" /></svg></button><Link href="/" aria-label="Beranda Tong Yi Group"><img :src="logo" alt="Tong Yi Group" width="1718" height="915" class="h-auto w-40 object-contain" /></Link><div class="mt-4 flex items-center gap-2"><span aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-accent"></span><p class="text-[10px] font-semibold tracking-[0.16em] text-muted">PORTAL ADMIN</p></div></div>
            <nav aria-label="Navigasi Admin" class="flex-1 overflow-y-auto px-4 py-5"><AdminNavigation :items="navigation" :path="path" prefix="desktop-nav" /></nav>
            <div class="border-t border-line p-5"><Link href="/" class="block rounded-lg px-3 py-2 text-xs font-medium text-muted hover:bg-page hover:text-brand">Lihat halaman depan</Link><div class="mt-4 flex items-center gap-3 rounded-xl bg-page p-3"><span class="account-avatar" aria-hidden="true">{{ initials }}</span><div class="min-w-0"><p class="truncate text-xs font-semibold text-brand">{{ account.name || 'Admin' }}</p><p class="mt-1 text-[10px] text-muted">{{ accountRole }}</p></div></div><form class="mt-3" @submit.prevent="signOut"><button :disabled="logout.processing" type="submit" class="w-full rounded-lg border border-line px-3 py-2.5 text-xs font-semibold text-muted hover:bg-page hover:text-brand disabled:opacity-50">{{ logout.processing ? 'Sedang keluar…' : 'Keluar' }}</button></form></div>
        </aside>

        <button v-if="sidebarCollapsed" id="admin-sidebar-open" type="button" aria-label="Buka sidebar" title="Buka sidebar" aria-controls="admin-desktop-sidebar" :aria-expanded="false" class="fixed left-0 top-24 z-40 hidden h-10 w-8 items-center justify-center rounded-r-lg border border-l-0 border-line bg-white text-muted shadow-sm hover:bg-brand-soft hover:text-brand lg:flex" @click="toggleSidebar"><svg aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="4" width="18" height="16" rx="2" /><path d="M9 4v16m4-11 3 3-3 3" /></svg></button>
        <div :class="['admin-frame min-w-0', sidebarCollapsed ? 'lg:pl-0' : 'lg:pl-60']">
            <header class="sticky top-0 z-30 border-b border-line bg-white/95 backdrop-blur-md">
                <div class="flex min-h-[76px] items-center justify-between gap-4 px-5 sm:px-8 lg:px-10"><div class="flex min-w-0 items-center gap-4"><button id="admin-menu-toggle" type="button" class="rounded-lg border border-line px-3 py-2 text-xs font-semibold text-brand lg:hidden" :aria-expanded="menuOpen" aria-controls="admin-mobile-navigation" @click="menuOpen = !menuOpen">{{ menuOpen ? 'Tutup menu' : 'Menu' }}</button><div class="min-w-0"><p class="text-[10px] font-medium tracking-wide text-muted">Tong Yi Group / Administrasi</p><p class="mt-1 truncate text-sm font-semibold text-brand">{{ title }}</p></div></div><div class="flex min-w-0 items-center gap-3"><button type="button" class="theme-toggle flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-line text-muted hover:bg-page" :aria-label="theme === 'dark' ? 'Gunakan mode terang' : 'Gunakan mode gelap'" :title="theme === 'dark' ? 'Mode terang' : 'Mode gelap'" :aria-pressed="theme === 'dark'" @click="toggleTheme"><svg v-if="theme === 'light'" aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M20.5 13A8.5 8.5 0 0 1 11 3.5 8.5 8.5 0 1 0 20.5 13Z" /></svg><svg v-else aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="4" /><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5" /></svg></button><div class="hidden text-right sm:block"><p class="max-w-40 truncate text-xs font-semibold text-brand">{{ account.name || 'Admin' }}</p><p class="mt-1 text-[10px] text-muted">{{ accountRole }}</p></div><span class="account-avatar shrink-0" aria-hidden="true">{{ initials }}</span></div></div>
                <nav v-if="menuOpen" id="admin-mobile-navigation" aria-label="Navigasi Admin seluler" class="border-t border-line px-5 py-4 lg:hidden"><AdminNavigation :items="navigation" :path="path" prefix="mobile-nav" /><Link href="/" class="block px-3 py-3 text-sm text-muted">Lihat halaman depan</Link><form @submit.prevent="signOut"><button type="submit" :disabled="logout.processing" class="px-3 py-3 text-sm font-semibold text-muted">Keluar</button></form></nav>
            </header>
            <main id="admin-content" class="w-full px-5 py-7 sm:px-8 lg:px-10"><AdminTabs /><slot v-if="adminWorkspace.active" /><p v-else class="py-16 text-center text-sm text-muted">Pilih menu untuk mulai bekerja.</p></main>
            <footer class="flex w-full flex-wrap items-center justify-between gap-2 px-5 pb-6 pt-3 text-[10px] text-muted/70 sm:px-8 lg:px-10"><p>Tong Yi Group</p><p>Ruang pengelolaan pembelajaran</p></footer>
        </div>
    </div>
</template>
