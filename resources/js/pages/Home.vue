<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import logo from '../../../assets/brand/tong-yi-group-logo.png';

const menuOpen = ref(false);
const programs = [
    { title: 'Mandarin', category: 'Bahasa', character: '文', subtitle: 'Satu program, beragam pilihan belajar.', description: 'Kenali pembelajaran Mandarin Tradisional dan Mandarin Sederhana, dengan subprogram dan tahapan belajar masing-masing.', tags: ['Mandarin Tradisional', 'Mandarin Sederhana'], tone: 'blue' },
];
const questions = [
    { question: 'Apakah Tong Yi Group hanya menyediakan program bahasa?', answer: 'Tong Yi Group menjadi wadah berbagai program pembelajaran. Bahasa merupakan salah satu kategori, dengan Mandarin sebagai program saat ini. Pilihan program lainnya akan ditampilkan ketika tersedia.' },
    { question: 'Bagaimana memilih program yang sesuai?', answer: 'Mulai dari kelompok peserta dan tujuan belajar. Rincian level, kurikulum, serta layanan akan ditampilkan setelah katalog program resmi tersedia.' },
    { question: 'Apakah tersedia kelas online dan offline?', answer: 'Pilihan online atau offline mengikuti layanan yang benar-benar dibuka pada setiap subprogram. Tidak semua program harus memiliki pilihan layanan yang sama.' },
    { question: 'Bagaimana susunan pembelajarannya?', answer: 'Pembelajaran disusun berdasarkan level dan paket. Satu paket normal terdiri dari delapan sesi, dengan rencana materi pada setiap sesi.' },
    { question: 'Apakah sudah bisa mendaftar melalui halaman ini?', answer: 'Belum. Halaman ini merupakan pengenalan awal. Informasi program, jadwal, biaya, dan pendaftaran akan tersedia setelah ditetapkan oleh Tong Yi Group.' },
];
</script>

<template>
    <Head>
        <title>同一集团</title>
        <meta name="description" content="Kenali Tong Yi Group, wadah berbagai program pembelajaran, dan temukan pilihan belajar sesuai tujuan Anda." />
    </Head>
    <div class="home-page bg-white text-ink">
        <a href="#konten" class="skip-link">Lewati ke konten</a>
        <header class="sticky top-0 z-40 border-b border-line bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-4 lg:px-10">
                <a href="#beranda" aria-label="Tong Yi Group — Beranda" class="shrink-0">
                    <img :src="logo" alt="Tong Yi Group" width="1718" height="915" class="h-auto w-36 object-contain sm:w-40" />
                </a>
                <nav aria-label="Navigasi utama" class="hidden items-center gap-9 text-sm font-semibold text-muted md:flex">
                    <a href="#beranda" class="text-brand">Beranda</a>
                    <a href="#tentang">Tentang kami</a>
                    <a href="#program">Program</a>
                    <a href="#pertanyaan">Tanya jawab</a>
                </nav>
                <a href="#program" class="hidden rounded-full bg-brand px-6 py-3 text-sm font-semibold text-white md:inline-flex">Jelajahi program</a>
                <button type="button" class="rounded-lg border border-line px-4 py-2 text-sm font-semibold md:hidden" :aria-expanded="menuOpen" aria-controls="mobile-menu" @click="menuOpen = !menuOpen">{{ menuOpen ? 'Tutup' : 'Menu' }}</button>
            </div>
            <nav v-if="menuOpen" id="mobile-menu" aria-label="Navigasi seluler" class="grid gap-4 border-t border-line px-6 py-5 text-sm font-semibold md:hidden">
                <a v-for="item in [{ name: 'Beranda', id: 'beranda' }, { name: 'Tentang kami', id: 'tentang' }, { name: 'Program', id: 'program' }, { name: 'Tanya jawab', id: 'pertanyaan' }]" :key="item.id" :href="`#${item.id}`" @click="menuOpen = false">{{ item.name }}</a>
            </nav>
        </header>

        <main id="konten">
            <section id="beranda" class="hero-section relative overflow-hidden">
                <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-6 py-16 lg:grid-cols-[1.1fr_1fr] lg:gap-16 lg:px-10 lg:py-24">
                    <div>
                        <p class="eyebrow"><span class="inline-block h-2 w-2 rounded-full bg-accent"></span> SELAMAT DATANG DI TONG YI GROUP</p>
                        <h1 class="mt-7 max-w-2xl text-5xl font-semibold leading-[1.1] tracking-tight text-brand sm:text-6xl lg:text-7xl">Potensi baru.<br />Wawasan baru.<br /><span class="hero-emphasis">Langkah baru.</span></h1>
                        <p class="mt-7 max-w-lg text-base leading-8 text-muted sm:text-lg">Mulai perjalanan belajar dan tumbuh bersama Tong Yi Group. Kenali program yang sesuai dengan tahap dan tujuan belajar Anda.</p>
                        <div class="mt-9 flex flex-wrap items-center gap-6">
                            <a href="#program" class="rounded-full bg-brand px-7 py-4 text-sm font-semibold text-white">Lihat program</a>
                            <a href="#tentang" class="border-b border-brand/30 py-2 text-sm font-semibold text-brand">Kenali Tong Yi Group</a>
                        </div>
                    </div>
                    <div class="hero-art relative mx-auto flex aspect-square w-full max-w-lg items-center justify-center" aria-label="Identitas Tong Yi Group">
                        <div class="hero-orbit" aria-hidden="true"></div>
                        <div class="hero-orbit-orange" aria-hidden="true"></div>
                        <span class="absolute right-8 top-7 text-sm font-medium tracking-[0.2em] text-brand/60" aria-hidden="true">BELAJAR & BERTUMBUH</span>
                        <div class="relative z-10 w-full px-6"><img :src="logo" alt="Tong Yi Group, belajar dan bertumbuh bersama" width="1718" height="915" class="h-auto w-full object-contain" /></div>
                        <span class="absolute bottom-9 left-4 rounded-full border border-white bg-white/90 px-5 py-3 text-sm font-medium text-brand shadow-sm">Belajar. Bertumbuh. Bersama.</span>
                        <span class="absolute right-6 top-24 text-4xl text-accent" aria-hidden="true">✦</span>
                    </div>
                </div>
                <div class="border-t border-brand/10 bg-white/60">
                    <div class="mx-auto grid max-w-7xl gap-5 px-6 py-6 text-sm font-medium text-brand sm:grid-cols-3 lg:px-10">
                        <p><span class="mr-3 text-interactive" aria-hidden="true">01 /</span> Program sesuai tahap belajar</p>
                        <p><span class="mr-3 text-interactive" aria-hidden="true">02 /</span> Materi tersusun per sesi</p>
                        <p><span class="mr-3 text-interactive" aria-hidden="true">03 /</span> Perjalanan belajar berkelanjutan</p>
                    </div>
                </div>
            </section>

            <section id="tentang" class="mx-auto grid max-w-7xl gap-8 px-6 py-20 lg:grid-cols-[0.7fr_1.3fr] lg:gap-24 lg:px-10 lg:py-24">
                <div><p class="eyebrow">MENGENAL TONG YI GROUP</p><p class="mt-5 text-5xl font-medium text-brand/15" aria-hidden="true">Bersama.</p></div>
                <div><h2 class="text-3xl font-semibold leading-tight tracking-tight text-brand sm:text-4xl">Setiap perjalanan besar<br class="hidden sm:block" /> dimulai dari kemauan belajar.</h2><p class="mt-6 max-w-2xl leading-8 text-muted">Tong Yi Group menjadi wadah berbagai program pembelajaran dan pengembangan diri. Bahasa adalah salah satu kategorinya, dengan Mandarin sebagai program saat ini. Peserta dapat mengenali pilihan pembelajaran berdasarkan kelompok peserta, level, dan layanan yang ditawarkan.</p><p class="mt-4 leading-8 text-muted">Kami memulai dari satu hal sederhana: memberi ruang untuk terus belajar dan berkembang bersama.</p></div>
            </section>

            <section id="program" class="bg-page px-6 py-20 lg:px-10 lg:py-24">
                <div class="mx-auto max-w-7xl">
                    <div class="flex flex-wrap items-end justify-between gap-7"><div><p class="eyebrow">TEMUKAN ARAH BELAJAR ANDA</p><h2 class="mt-4 text-3xl font-semibold tracking-tight text-brand sm:text-4xl">Kenali program kami.</h2></div></div>
                    <p class="mt-5 max-w-2xl text-sm leading-6 text-muted">Kenali program Mandarin dalam kategori Bahasa. Informasi subprogram, layanan, jadwal, dan biaya akan dilengkapi setelah ditetapkan.</p>
                    <div class="mt-9 grid gap-7 md:grid-cols-2" aria-live="polite" aria-atomic="true">
                        <article v-for="program in programs" :key="program.title" class="overflow-hidden rounded-2xl border border-line bg-white">
                            <div :class="['program-art relative flex h-48 items-center justify-between overflow-hidden px-8 sm:h-56 sm:px-10', program.tone === 'warm' ? 'program-art-warm' : 'program-art-blue']"><div class="relative z-10"><span class="rounded-full border border-current/20 px-3 py-1.5 text-xs font-semibold">{{ program.category }}</span><p class="mt-6 max-w-48 text-xl font-semibold leading-7">{{ program.subtitle }}</p></div><span class="program-character" lang="zh" aria-hidden="true">{{ program.character }}</span></div>
                            <div class="p-7 sm:p-8"><div class="flex gap-2"><span v-for="tag in program.tags" :key="tag" class="rounded-full bg-page px-3 py-1 text-xs font-medium text-muted">{{ tag }}</span></div><h3 class="mt-4 text-2xl font-semibold text-brand">{{ program.title }}</h3><p class="mt-3 leading-7 text-muted">{{ program.description }}</p><details class="mt-6 border-t border-line pt-5"><summary class="cursor-pointer text-sm font-semibold text-brand">Tentang pilihan program</summary><p class="mt-3 text-sm leading-7 text-muted">Mandarin Tradisional (Traditional) dan Mandarin Sederhana (Simplified) memiliki pengaturan subprogram yang independen. Level, kurikulum, paket, harga, dan kapasitas dapat berbeda. Kelompok peserta mengikuti subprogram yang tersedia.</p></details></div>
                        </article>
                    </div>
                    <div class="mt-8 border-l-4 border-accent pl-6 py-2"><h3 class="text-lg font-semibold text-brand">Beragam bidang, ruang untuk berkembang.</h3><p class="mt-2 max-w-2xl text-sm leading-7 text-muted">Pilihan belajar di Tong Yi Group dapat terus bertambah di berbagai kategori. Temukan program lainnya di sini ketika tersedia.</p></div>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-6 py-20 lg:px-10 lg:py-24" aria-labelledby="journey-title">
                <div class="max-w-xl"><p class="eyebrow">SELANGKAH DEMI SELANGKAH</p><h2 id="journey-title" class="mt-4 text-3xl font-semibold tracking-tight text-brand sm:text-4xl">Kenali alurnya.<br />Temukan langkah pertama.</h2></div>
                <div class="mt-12 grid gap-9 md:grid-cols-3"><div v-for="(step, index) in [{ title: 'Kenali program', text: 'Lihat kelompok peserta dan gambaran pembelajaran yang sesuai dengan kebutuhan Anda.' }, { title: 'Pelajari pilihan kelas', text: 'Saat kelas tersedia, periksa level, guru, jadwal, lokasi atau media belajar, serta biaya.' }, { title: 'Mulai perjalanan belajar', text: 'Ikuti proses pendaftaran dan pembayaran setelah detail kelas tersedia dan disetujui.' }]" :key="step.title" class="border-t border-line pt-6"><span class="text-sm font-semibold text-interactive">0{{ index + 1 }}</span><h3 class="mt-5 text-xl font-semibold text-brand">{{ step.title }}</h3><p class="mt-3 leading-7 text-muted">{{ step.text }}</p></div></div>
            </section>

            <section id="pertanyaan" class="border-y border-line bg-page px-6 py-20 lg:px-10">
                <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-24"><div><p class="eyebrow">SEBELUM MULAI</p><h2 class="mt-4 text-3xl font-semibold tracking-tight text-brand sm:text-4xl">Ada yang ingin<br />Anda ketahui?</h2><p class="mt-5 max-w-sm leading-7 text-muted">Beberapa hal untuk membantu Anda mengenal pembelajaran di Tong Yi Group.</p></div><div><details v-for="item in questions" :key="item.question" class="faq-item border-b border-line py-5 first:pt-0"><summary class="cursor-pointer pr-5 font-semibold leading-7 text-brand">{{ item.question }}</summary><p class="mt-4 pr-5 text-sm leading-7 text-muted">{{ item.answer }}</p></details></div></div>
            </section>

            <section class="bg-brand px-6 py-16 text-white lg:px-10"><div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-8"><div><p class="text-xs font-semibold tracking-[0.18em] text-white/70">TONG YI GROUP</p><h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">Ruang untuk belajar.<br />Kesempatan untuk bertumbuh.</h2></div><a href="#program" class="rounded-full bg-accent px-7 py-4 text-sm font-semibold text-ink">Jelajahi gambaran program</a></div></section>
        </main>
        <footer class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-5 px-6 py-8 text-sm text-muted lg:px-10"><p class="font-semibold text-brand">Tong Yi Group <span class="ml-3 font-normal text-muted">同一集团</span></p><p>Belajar dan bertumbuh bersama.</p><div class="flex items-center gap-6"><a href="/admin/login" class="font-medium text-brand">Portal Admin</a><a href="#beranda" class="font-medium text-brand">Kembali ke atas</a></div></footer>
    </div>
</template>

<style scoped>
.home-page { overflow-wrap: anywhere; }
.eyebrow { display: flex; align-items: center; gap: .65rem; font-size: .7rem; font-weight: 700; letter-spacing: .16em; color: var(--color-interactive); }
.hero-section { background: linear-gradient(115deg, var(--color-page), var(--color-brand-soft) 75%, var(--color-accent-soft)); }
.hero-emphasis { position: relative; z-index: 0; white-space: nowrap; }
.hero-emphasis::after { content: ''; position: absolute; left: 0; right: 0; bottom: .04em; height: .17em; z-index: -1; background: var(--color-accent); opacity: .7; border-radius: 50%; }
.hero-art { background: radial-gradient(circle, white 0%, white 39%, transparent 40%); }
.hero-orbit { position: absolute; inset: 6%; border: 1px solid color-mix(in srgb, var(--color-brand) 15%, transparent); border-radius: 50%; transform: rotate(-15deg); }
.hero-orbit::before { content: ''; position: absolute; inset: -8%; border: 1px dashed color-mix(in srgb, var(--color-brand) 12%, transparent); border-radius: 50%; }
.hero-orbit::after { content: ''; position: absolute; top: 50%; left: -5px; width: 10px; height: 10px; border-radius: 50%; background: var(--color-interactive); box-shadow: 0 0 0 5px color-mix(in srgb, var(--color-interactive) 9%, transparent); }
.hero-art > .hero-orbit, .hero-orbit-orange { pointer-events: none; }
.hero-orbit-orange { position: absolute; inset: -1%; border-radius: 50%; transform: rotate(15deg); }
.hero-orbit-orange::after { content: ''; position: absolute; top: 50%; right: -5px; width: 10px; height: 10px; border-radius: 50%; background: var(--color-accent); box-shadow: 0 0 0 5px color-mix(in srgb, var(--color-accent) 12%, transparent); }
@media (prefers-reduced-motion: no-preference) {
    .hero-orbit { animation: portal-orbit 48s linear infinite; }
    .hero-orbit-orange { animation: portal-orange-orbit 48s linear infinite; }
    .hero-orbit::before { animation: portal-counter-orbit 72s linear infinite; }
    a, button { transition: color .2s ease, background-color .2s ease, box-shadow .2s ease; }
    a.rounded-full:hover { box-shadow: 0 5px 16px color-mix(in srgb, var(--color-brand) 16%, transparent); }
    section:target h2, section:target h1 { animation: section-arrive .65s ease-out; }
    article { transition: transform .25s ease, box-shadow .25s ease; }
    article:hover { transform: translateY(-4px); box-shadow: 0 12px 28px color-mix(in srgb, var(--color-brand) 7%, transparent); }
}
@keyframes portal-orbit { from { transform: rotate(-15deg); } to { transform: rotate(345deg); } }
@keyframes portal-orange-orbit { from { transform: rotate(15deg); } to { transform: rotate(-345deg); } }
@keyframes portal-counter-orbit { from { transform: rotate(0deg); } to { transform: rotate(-360deg); } }
@keyframes section-arrive { from { opacity: .65; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
.program-art-warm { background: var(--color-accent-soft); color: var(--color-brand); }
.program-art-blue { background: var(--color-brand-soft); color: var(--color-brand); }
.program-character { font-family: serif; font-size: 10rem; line-height: 1; opacity: .17; transform: rotate(-9deg); }
.skip-link { position: fixed; top: -5rem; left: 1rem; z-index: 50; padding: 1rem; background: white; color: var(--color-brand); border: 2px solid var(--color-brand); }
.skip-link:focus { top: 1rem; }
a, button, summary { outline-offset: 5px; }
a:focus-visible, button:focus-visible, summary:focus-visible { outline: 2px solid var(--color-interactive); }
nav a:hover { color: var(--color-interactive); }
section[id] { scroll-margin-top: 8rem; }
@media (max-width: 380px) { .hero-emphasis { white-space: normal; } .program-character { font-size: 7rem; } }
</style>
