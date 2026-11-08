<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '../../layouts/AdminLayout.vue';
defineProps({ admin: { type: Object, required: true }, statistics: { type: Object, default: null }, recentPrograms: { type: Array, required: true } });
const abilities = usePage().props.auth?.abilities ?? {};
const date = (value) => new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'Asia/Pontianak' }).format(new Date(value));
const cards = [
    { key: 'total', title: 'Total program', detail: 'Seluruh program dalam katalog', accent: 'brand' },
    { key: 'drafts', title: 'Belum diterbitkan', detail: 'Draf yang dapat Anda lanjutkan', accent: 'orange' },
    { key: 'published', title: 'Program terbit', detail: 'Memiliki versi terbit di katalog Admin', accent: 'blue' },
];
</script>

<template>
    <Head title="Dasbor Admin" />
    <AdminLayout>
        <div class="flex flex-wrap items-end justify-between gap-5"><div><p class="text-[10px] font-semibold tracking-[0.17em] text-interactive">RINGKASAN ADMIN</p><h1 class="mt-3 text-2xl font-semibold tracking-tight text-brand sm:text-3xl">Dasbor</h1><p class="mt-2 text-sm leading-7 text-muted">Selamat datang, {{ admin.name }}. Lanjutkan pengelolaan program Anda.</p></div><Link v-if="abilities['programs.manage']" href="/admin/programs/create" class="admin-button">Tambah program</Link></div>

        <template v-if="statistics"><div class="mt-8 grid gap-4 sm:grid-cols-3"><section v-for="card in cards" :key="card.key" class="rounded-xl border border-line bg-white p-5 sm:p-6"><div class="flex items-center justify-between gap-2"><h2 class="text-xs font-medium text-muted">{{ card.title }}</h2><span aria-hidden="true" :class="['h-1.5 w-5 rounded-full', card.accent === 'orange' ? 'bg-accent' : card.accent === 'blue' ? 'bg-interactive' : 'bg-brand']"></span></div><p class="mt-5 text-3xl font-semibold tracking-tight text-brand">{{ statistics[card.key] }}</p><p class="mt-3 text-[11px] leading-5 text-muted">{{ card.detail }}</p></section></div>

        <div class="mt-7">
            <section class="min-w-0 overflow-hidden rounded-xl border border-line bg-white"><div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-6 py-5"><div><h2 class="text-sm font-semibold text-brand">Program terbaru</h2><p class="mt-1 text-xs text-muted">Lima program yang terakhir diperbarui.</p></div><Link href="/admin/programs" class="text-xs font-semibold text-interactive">Kelola program</Link></div>
                <div v-if="!recentPrograms.length" class="px-6 py-16 text-center"><div class="mx-auto mb-5 h-1 w-10 rounded bg-accent"></div><h3 class="text-base font-semibold text-brand">Katalog program Anda dimulai di sini</h3><p class="mx-auto mt-3 max-w-sm text-sm leading-7 text-muted">Tambahkan program pertama, susun subprogram, lalu lengkapi layanan dan kurikulumnya.</p><Link v-if="abilities['programs.manage']" href="/admin/programs/create" class="admin-secondary mt-6">Buat program pertama</Link></div>
                <div v-else class="relative overflow-x-auto"><table class="w-full min-w-[460px] text-left text-xs"><caption class="sr-only">Program yang terakhir diperbarui</caption><thead class="bg-page/60 text-muted"><tr><th scope="col" class="px-6 py-3 font-medium">Program</th><th scope="col" class="px-4 py-3 font-medium">Status</th><th scope="col" class="px-4 py-3 font-medium">Diperbarui</th><th scope="col" class="px-5 py-3 font-medium">Tindakan</th></tr></thead><tbody><tr v-for="program in recentPrograms" :key="program.id" class="border-t border-line"><td class="px-6 py-4"><p class="font-semibold text-brand">{{ program.name }}</p><p class="mt-1.5 text-[11px] text-muted">{{ program.category || 'Kategori belum diisi' }}</p></td><td class="px-4 py-4"><span :class="['whitespace-nowrap rounded-full px-2.5 py-1 text-[10px] font-medium', program.published ? 'bg-brand-soft text-brand' : 'bg-accent-soft text-ink']">{{ program.published ? 'Terbit' : 'Draf' }}</span><p v-if="program.has_changes" class="mt-2 whitespace-nowrap text-[10px] text-muted">Ada perubahan draf</p></td><td class="whitespace-nowrap px-4 py-4 text-muted">{{ date(program.updated_at) }}</td><td class="px-5 py-4"><Link v-if="abilities['programs.manage']" :href="`/admin/programs/${program.id}/edit`" class="font-semibold text-interactive" :aria-label="`Ubah ${program.name}`">Ubah</Link></td></tr></tbody></table></div>
            </section>

        </div>
        </template><section v-else class="mt-8 rounded-xl border border-line bg-white p-7"><h2 class="text-lg font-semibold text-brand">Ruang kerja Anda</h2><p class="mt-3 text-sm leading-7 text-muted">Menu yang tersedia mengikuti izin akun Anda. Hubungi Superadmin jika memerlukan akses Program Akademik.</p></section>
    </AdminLayout>
</template>
