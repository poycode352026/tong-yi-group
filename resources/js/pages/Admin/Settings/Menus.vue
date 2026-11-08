<script setup>
import { computed, nextTick, onBeforeUnmount, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { useAdminFormBuffer } from '../../../utils/useAdminFormBuffer';
import AdminLayout from '../../../layouts/AdminLayout.vue';
const props = defineProps({ menus: Array });
const groupOf = (menu) => menu.key === 'dashboard' ? 'main' : ['users', 'menus', 'activity', 'archives'].includes(menu.key) ? 'system' : 'academic';
const groupLabels = { main: 'Utama', academic: 'Akademik', settings: 'Pengaturan', system: 'Sistem' };
const groupOrder = { main: 0, academic: 1, settings: 2, system: 3 };
const sorted = (menus) => JSON.parse(JSON.stringify(menus)).sort((a, b) => groupOrder[groupOf(a)] - groupOrder[groupOf(b)] || a.position - b.position);
const form = useForm({ menus: sorted(props.menus) });
const workspaceBuffer = useAdminFormBuffer('menus', form);
const visibleMenus = computed(() => form.menus.filter((item) => item.key !== 'frameworks'));
const dragging = ref(null);
const announcement = ref('');
let originalOrder = [];
let pointerId = null;
const canMove = (menu) => visibleMenus.value.filter((item) => groupOf(item) === groupOf(menu)).length > 1;
function renumber() { form.menus.forEach((menu, i) => { menu.position = i + 1; }); form.clearErrors(); }
function stopDrag(cancel = false) {
    if (!dragging.value) return;
    if (cancel) {
        form.menus.sort((a, b) => originalOrder.indexOf(a.key) - originalOrder.indexOf(b.key));
    } else {
        renumber();
        announcement.value = 'Urutan diubah. Simpan menu untuk menerapkannya.';
    }
    dragging.value = null;
    pointerId = null;
    window.removeEventListener('pointermove', moveDrag);
    window.removeEventListener('pointerup', endDrag);
    window.removeEventListener('pointercancel', cancelDrag);
    window.removeEventListener('keydown', escapeDrag);
}
const endDrag = (event) => { if (event.pointerId === pointerId) stopDrag(); };
const cancelDrag = () => stopDrag(true);
const escapeDrag = (event) => { if (event.key === 'Escape') { event.preventDefault(); stopDrag(true); } };
function startDrag(event, menu) {
    if (event.button !== 0 || form.processing || !canMove(menu)) return;
    event.preventDefault();
    event.currentTarget.focus();
    originalOrder = form.menus.map((item) => item.key);
    dragging.value = menu.key;
    pointerId = event.pointerId;
    event.currentTarget.setPointerCapture(event.pointerId);
    window.addEventListener('pointermove', moveDrag, { passive: false });
    window.addEventListener('pointerup', endDrag);
    window.addEventListener('pointercancel', cancelDrag);
    window.addEventListener('keydown', escapeDrag);
}
function moveDrag(event) {
    if (event.pointerId !== pointerId) return;
    event.preventDefault();
    const row = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-menu-key]');
    const from = form.menus.findIndex((menu) => menu.key === dragging.value);
    const to = form.menus.findIndex((menu) => menu.key === row?.dataset.menuKey);
    if (from < 0 || to < 0 || from === to || groupOf(form.menus[from]) !== groupOf(form.menus[to])) return;
    const box = row.getBoundingClientRect();
    if (from < to ? event.clientY < box.top + box.height / 2 : event.clientY > box.top + box.height / 2) return;
    form.menus.splice(to, 0, form.menus.splice(from, 1)[0]);
}
async function moveWithKeyboard(event, menu) {
    if (!['ArrowUp', 'ArrowDown'].includes(event.key) || dragging.value || form.processing) return;
    event.preventDefault();
    const from = form.menus.findIndex((item) => item.key === menu.key);
    const direction = event.key === 'ArrowUp' ? -1 : 1;
    let to = from + direction;
    while (form.menus[to]?.key === 'frameworks') to += direction;
    if (!form.menus[to] || groupOf(menu) !== groupOf(form.menus[to])) return;
    form.menus.splice(to, 0, form.menus.splice(from, 1)[0]);
    renumber();
    announcement.value = `${menu.label} dipindahkan. Simpan menu untuk menerapkannya.`;
    await nextTick();
    document.getElementById(`drag-${menu.key}`)?.focus();
}
onBeforeUnmount(() => stopDrag(true));
const page = usePage();
function save() {
    if (dragging.value) return;
    form.put('/admin/settings/menus', {
        preserveScroll: true,
        onSuccess: () => { form.menus = sorted(page.props.menus); form.defaults(); workspaceBuffer.saved(); },
    });
}
</script>
<template>
    <Head title="Manajemen Menu" />
    <AdminLayout>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div><h1 class="text-2xl font-semibold text-ink">Manajemen Menu</h1><p class="mt-2 text-sm text-muted">Atur nama, urutan, dan tampilan menu.</p></div>
            <button type="submit" form="menu-settings-form" class="admin-button" :disabled="form.processing || !!dragging">{{ form.processing ? 'Menyimpan…' : 'Simpan menu' }}</button>
        </div>
        <p v-if="page.props.flash?.success" role="status" class="mt-5 rounded-lg bg-brand-soft p-4 text-sm text-brand">{{ page.props.flash.success }}</p>
        <div v-if="Object.keys(form.errors).length" role="alert" class="mt-5 rounded-lg bg-red-50 p-4 text-xs leading-6 text-red-700"><p v-for="(error, key) in form.errors" :key="key">{{ error }}</p></div>
        <p class="sr-only" role="status" aria-live="polite">{{ announcement }}</p>
        <form id="menu-settings-form" class="mt-6" @submit.prevent="save">
            <div class="overflow-hidden rounded-xl border border-line bg-white">
                <div role="region" aria-label="Tabel pengaturan menu" tabindex="0" class="overflow-x-auto focus-visible:outline-2 focus-visible:outline-interactive">
                    <table class="menu-table w-full min-w-[700px] text-left text-sm">
                        <caption class="sr-only">Pengaturan nama, keterangan, urutan, dan visibilitas menu Admin</caption>
                        <thead class="border-b border-line bg-page text-xs text-muted"><tr><th scope="col" class="w-12 px-3 py-3"><span class="sr-only">Geser urutan</span></th><th scope="col" class="w-[28%] px-5 py-3 font-semibold">Nama menu</th><th scope="col" class="px-4 py-3 font-semibold">Keterangan</th><th scope="col" class="w-32 px-5 py-3 text-center font-semibold">Tampilkan</th></tr></thead>
                        <tbody class="divide-y divide-line">
                            <template v-for="(menu, index) in visibleMenus" :key="menu.key">
                            <tr v-if="index === 0 || groupOf(visibleMenus[index - 1]) !== groupOf(menu)" class="bg-page/50"><th colspan="4" scope="rowgroup" class="px-5 py-2 text-xs font-medium text-muted">{{ groupLabels[groupOf(menu)] }}</th></tr>
                            <tr :data-menu-key="menu.key" :class="['hover:bg-page/40', dragging === menu.key && 'dragging-row']">
                                <td class="px-3 py-3 text-center"><button v-if="canMove(menu)" :id="`drag-${menu.key}`" type="button" class="drag-handle" :aria-label="`Geser ${menu.label}`" aria-describedby="drag-menu-help" :disabled="form.processing" @pointerdown="startDrag($event, menu)" @keydown="moveWithKeyboard($event, menu)"><svg aria-hidden="true" width="16" height="20" viewBox="0 0 16 20" fill="currentColor"><circle v-for="(point, i) in [[5,5],[11,5],[5,10],[11,10],[5,15],[11,15]]" :key="i" :cx="point[0]" :cy="point[1]" r="1.3" /></svg></button></td>
                                <td class="px-5 py-3 align-top"><input :id="`menus.${index}.label`" v-model="menu.label" :aria-label="`Nama menu ${menu.key}`" :aria-invalid="!!form.errors[`menus.${index}.label`]" :aria-describedby="form.errors[`menus.${index}.label`] ? `menu-label-error-${index}` : undefined" maxlength="80" class="admin-input" :disabled="form.processing" /><p v-if="form.errors[`menus.${index}.label`]" :id="`menu-label-error-${index}`" class="field-error">{{ form.errors[`menus.${index}.label`] }}</p></td>
                                <td class="px-4 py-3 align-top"><input :id="`menus.${index}.description`" v-model="menu.description" :aria-label="`Keterangan menu ${menu.key}`" :aria-invalid="!!form.errors[`menus.${index}.description`]" :aria-describedby="form.errors[`menus.${index}.description`] ? `menu-description-error-${index}` : undefined" maxlength="160" class="admin-input" :disabled="form.processing" /><p v-if="form.errors[`menus.${index}.description`]" :id="`menu-description-error-${index}`" class="field-error">{{ form.errors[`menus.${index}.description`] }}</p></td>

                                <td class="px-5 py-3 text-center align-middle"><input v-model="menu.is_visible" type="checkbox" :aria-label="`Tampilkan menu ${menu.key}`" :disabled="menu.locked || form.processing" :aria-describedby="menu.locked ? 'locked-menu-note' : undefined" class="h-4 w-4 accent-interactive disabled:opacity-50" /><span v-if="menu.locked" class="mt-1 block text-[10px] text-muted">Wajib</span></td>
                            </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-line px-5 py-3 text-xs leading-6 text-muted"><p id="locked-menu-note">Menu wajib selalu tampil sesuai izin akun.</p><p id="drag-menu-help">Geser pegangan untuk mengurutkan dalam kelompok. Keyboard: panah atas/bawah.</p><p>Menyembunyikan menu tidak mencabut izin akses.</p></div>
            </div>
        </form>
    </AdminLayout>
</template>
<style scoped>
.menu-table .admin-input { padding: .5rem .65rem; font-size: .8rem; border-radius: 6px; }
.drag-handle { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 36px; border-radius: 5px; color: var(--color-muted); cursor: grab; touch-action: none; user-select: none; }
.drag-handle:hover { background: var(--color-page); color: var(--color-brand); }
.drag-handle:active { cursor: grabbing; }
.drag-handle:focus-visible { outline: 2px solid var(--color-interactive); outline-offset: 2px; }
.dragging-row { background: var(--color-brand-soft); }
</style>
