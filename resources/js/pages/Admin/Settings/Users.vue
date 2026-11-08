<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { useAdminFormBuffer } from '../../../utils/useAdminFormBuffer';
import AdminLayout from '../../../layouts/AdminLayout.vue';
const props = defineProps({ users: Object, permissions: Object, actorId: Number, search: String });
const term = ref(props.search);
const selected = ref(null);
const form = useForm({ is_admin: false, is_superadmin: false, permissions: [], access_revision: 1 });
const workspaceBuffer = useAdminFormBuffer('users', form, { selected, term });
function edit(user) { selected.value = user; form.defaults({ is_admin: user.is_admin, is_superadmin: user.is_superadmin, permissions: [...user.permissions], access_revision: user.access_revision }); form.reset(); workspaceBuffer.baseline(); form.clearErrors(); }
function selectPermission(key, checked) {
    const set = new Set(form.permissions);
    if (checked) { set.add(key); if (key !== 'programs.view') set.add('programs.view'); if (key === 'programs.publish') set.add('programs.manage'); }
    else { set.delete(key); if (key === 'programs.view') { set.delete('programs.manage'); set.delete('programs.publish'); } if (key === 'programs.manage') set.delete('programs.publish'); }
    form.permissions = [...set];
}
function changeAdmin() { if (!form.is_admin) { form.is_superadmin = false; form.permissions = []; } }
function save() { form.put(`/admin/settings/users/${selected.value.id}`, { preserveScroll: true, onSuccess: () => { selected.value = null; workspaceBuffer.saved(); } }); }
const page = usePage();
</script>
<template>
    <Head title="Pengguna & Hak Akses" />
    <AdminLayout><div><p class="text-[10px] font-semibold tracking-widest text-interactive">SUPERADMIN</p><h1 class="mt-3 text-2xl font-semibold text-brand">Pengguna & Hak Akses</h1><p class="mt-3 max-w-2xl text-sm leading-7 text-muted">Atur akses Admin, peran Superadmin, dan izin pengelolaan program.</p></div><p v-if="page.props.flash?.success" role="status" class="mt-5 rounded-lg bg-brand-soft p-4 text-sm text-brand">{{ page.props.flash.success }}</p>
        <form class="mt-6 flex max-w-lg gap-3" role="search" @submit.prevent="router.get('/admin/settings/users', { search: term }, { preserveState: true })"><label for="user-search" class="sr-only">Cari pengguna</label><input id="user-search" v-model="term" class="admin-input" placeholder="Cari nama atau email" /><button class="admin-secondary">Cari</button></form>
        <div class="mt-6 grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]"><section class="relative min-w-0 overflow-x-auto rounded-xl border border-line bg-white"><table class="w-full min-w-[480px] text-left text-sm"><caption class="sr-only">Akun pengguna</caption><thead class="bg-page text-xs text-muted"><tr><th class="p-4">Pengguna</th><th class="p-4">Peran</th><th class="p-4">Tindakan</th></tr></thead><tbody><tr v-for="user in users.data" :key="user.id" class="border-t border-line"><td class="p-4"><p class="font-semibold text-brand">{{ user.name }}</p><p class="mt-1 text-xs text-muted">{{ user.email }}</p></td><td class="p-4 text-xs">{{ user.is_superadmin ? 'Superadmin' : user.is_admin ? 'Admin' : 'Tanpa akses Admin' }}</td><td class="p-4"><button class="text-xs font-semibold text-interactive" type="button" :aria-label="`Atur akses ${user.name}`" @click="edit(user)">Atur akses</button></td></tr><tr v-if="!users.data.length"><td colspan="3" class="p-8 text-center text-muted">Pengguna tidak ditemukan.</td></tr></tbody></table><nav v-if="users.last_page > 1" class="flex gap-4 border-t border-line p-4 text-xs"><Link v-if="users.prev_page_url" :href="users.prev_page_url">Sebelumnya</Link><span>{{ users.current_page }} / {{ users.last_page }}</span><Link v-if="users.next_page_url" :href="users.next_page_url">Berikutnya</Link></nav></section>
            <section class="rounded-xl border border-line bg-white p-6"><template v-if="selected"><h2 class="text-base font-semibold text-brand">Akses {{ selected.name }}</h2><p class="mt-2 break-words text-xs text-muted">{{ selected.email }}</p><form class="mt-6 space-y-5" @submit.prevent="save"><label class="flex items-center gap-3 text-sm"><input v-model="form.is_admin" type="checkbox" :disabled="selected.id === actorId" @change="changeAdmin" /> Akses ruang Admin</label><label class="flex items-center gap-3 text-sm"><input v-model="form.is_superadmin" type="checkbox" :disabled="!form.is_admin || selected.id === actorId" /> Superadmin</label><p class="text-xs leading-6 text-muted">Superadmin dapat mengatur akses dan menu serta memiliki seluruh izin program.</p><fieldset :disabled="!form.is_admin || form.is_superadmin" class="space-y-4 border-t border-line pt-5"><legend class="pt-4 text-xs font-semibold text-brand">Hak akses</legend><label v-for="(label, key) in permissions" :key="key" class="flex items-start gap-3 text-xs leading-6"><input :checked="form.is_superadmin || form.permissions.includes(key)" type="checkbox" class="mt-1.5" @change="selectPermission(key, $event.target.checked)" /><span>{{ label }}</span></label></fieldset><div v-if="Object.keys(form.errors).length" role="alert" class="rounded-lg bg-red-50 p-3 text-xs leading-6 text-red-700"><p v-for="error in form.errors" :key="error">{{ error }}</p></div><button class="admin-button" :disabled="form.processing">Simpan akses</button></form></template><template v-else><h2 class="text-sm font-semibold text-brand">Pilih pengguna</h2><p class="mt-3 text-sm leading-7 text-muted">Pilih Atur akses untuk meninjau dan mengubah kewenangan akun. Perubahan diperiksa di server dan dicatat dalam riwayat.</p></template></section>
        </div>
    </AdminLayout>
</template>
