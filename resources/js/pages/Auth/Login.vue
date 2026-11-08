<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import logo from '../../../../assets/brand/tong-yi-group-logo.png';

const showPassword = ref(false);
const form = useForm({ email: '', password: '' });
const submit = () => form.post('/admin/login', { onFinish: () => form.reset('password') });
</script>

<template>
    <Head title="Masuk Admin" />
    <main class="grid min-h-screen bg-page text-ink lg:grid-cols-2">
        <section class="relative hidden flex-col justify-between overflow-hidden bg-brand p-14 text-white lg:flex">
            <Link href="/" class="w-fit text-sm font-semibold text-white/80 hover:text-white">Kembali ke beranda</Link>
            <div class="relative z-10 max-w-lg py-16"><p class="text-xs font-semibold tracking-[0.2em] text-accent">PORTAL ADMIN TONG YI</p><h1 class="mt-6 text-5xl font-semibold leading-tight tracking-tight">Ruang untuk mengelola.<br />Langkah untuk bertumbuh.</h1><p class="mt-6 text-lg leading-8 text-white/75">Kelola perjalanan pembelajaran Tong Yi Group dari satu ruang kerja.</p></div>
            <p class="text-sm text-white/65">同一集团 · Belajar dan bertumbuh bersama.</p>
            <div aria-hidden="true" class="pointer-events-none absolute -bottom-48 -right-48 h-[36rem] w-[36rem] rounded-full border border-white/10"></div>
        </section>
        <section class="flex items-center justify-center px-6 py-12 sm:px-12">
            <div class="w-full max-w-md">
                <Link href="/" aria-label="Beranda Tong Yi Group"><img :src="logo" alt="Tong Yi Group" width="1718" height="915" class="mb-10 h-auto w-52 max-w-full object-contain" /></Link>
                <p class="text-xs font-semibold tracking-[0.18em] text-interactive">SELAMAT DATANG KEMBALI</p><h2 class="mt-3 text-3xl font-semibold tracking-tight text-brand">Masuk ke ruang Admin</h2><p class="mt-3 leading-7 text-muted">Gunakan akun Admin yang telah diberikan kepada Anda.</p>
                <form class="mt-8 space-y-6" @submit.prevent="submit">
                    <div><label for="email" class="mb-2 block text-sm font-semibold">Alamat email</label><input id="email" v-model="form.email" type="email" name="email" autocomplete="username" required autofocus maxlength="255" :aria-invalid="!!form.errors.email" :aria-describedby="form.errors.email ? 'email-error' : undefined" class="w-full rounded-xl border border-line bg-white px-4 py-3 outline-offset-4 focus:outline-interactive" placeholder="nama@contoh.com" /><p v-if="form.errors.email" id="email-error" role="alert" class="mt-2 text-sm text-red-700">{{ form.errors.email }}</p></div>
                    <div><label for="password" class="mb-2 block text-sm font-semibold">Kata sandi</label><div class="flex rounded-xl border border-line bg-white focus-within:outline-2 focus-within:outline-offset-4 focus-within:outline-interactive"><input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" name="password" autocomplete="current-password" required :aria-invalid="!!form.errors.password" :aria-describedby="form.errors.password ? 'password-error' : undefined" class="min-w-0 flex-1 rounded-xl px-4 py-3 outline-none" /><button type="button" :aria-pressed="showPassword" aria-controls="password" class="rounded-xl px-4 text-xs font-semibold text-brand focus-visible:outline-2 focus-visible:outline-interactive" @click="showPassword = !showPassword">{{ showPassword ? 'Sembunyikan' : 'Tampilkan' }}</button></div><p v-if="form.errors.password" id="password-error" role="alert" class="mt-2 text-sm text-red-700">{{ form.errors.password }}</p></div>
                    <button type="submit" :disabled="form.processing" class="w-full rounded-xl bg-brand px-6 py-3.5 text-sm font-semibold text-white outline-offset-4 hover:bg-interactive focus-visible:outline-2 focus-visible:outline-interactive disabled:cursor-wait disabled:opacity-60">{{ form.processing ? 'Sedang masuk…' : 'Masuk' }}</button>
                </form>
                <p class="mt-6 text-sm leading-6 text-muted">Belum memiliki akses? Hubungi pengelola Tong Yi Group.</p><Link href="/" class="mt-8 inline-block text-sm font-semibold text-brand lg:hidden">Kembali ke beranda</Link>
            </div>
        </section>
    </main>
</template>
