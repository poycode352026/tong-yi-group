<?php

return [
    'permissions' => [
        'learning.manage' => 'Mengelola modul, bahan referensi, ujian, dan kunci jawaban',
        'programs.view' => 'Melihat daftar dan ringkasan program',
        'programs.manage' => 'Menambah, mengubah, dan menyimpan draf program',
        'programs.publish' => 'Menerbitkan program dan perubahan',
    ],
    'menus' => [
        'dashboard' => ['path' => '/admin', 'permission' => 'access-admin', 'label' => 'Dasbor', 'description' => 'Ringkasan ruang kerja', 'locked' => true],
        'programs' => ['path' => '/admin/programs', 'permission' => 'programs.view', 'label' => 'Program Akademik', 'description' => 'Program dan kurikulum', 'locked' => false],
        'frameworks' => ['path' => '/admin/learning/frameworks', 'permission' => 'learning.manage', 'label' => 'Acuan Pembelajaran', 'description' => 'Standar dan cakupan materi', 'locked' => false, 'hidden' => true],
        'materials' => ['path' => '/admin/learning/materials', 'permission' => 'learning.manage', 'label' => 'Modul', 'description' => 'Modul dan referensi mengajar', 'locked' => false],
        'assessments' => ['path' => '/admin/learning/assessments', 'permission' => 'learning.manage', 'label' => 'Ujian', 'description' => 'Soal per paket dan akhir level', 'locked' => false],
        'activity' => ['path' => '/admin/system/activity', 'permission' => 'manage-access', 'label' => 'Riwayat Aktivitas', 'description' => 'Perubahan dan aktivitas sistem', 'locked' => true, 'group' => 'system'],
        'archives' => ['path' => '/admin/system/archives', 'permission' => 'manage-access', 'label' => 'Arsip Level', 'description' => 'Level yang dikeluarkan dari program', 'locked' => true, 'group' => 'system'],
        'users' => ['path' => '/admin/settings/users', 'permission' => 'manage-access', 'label' => 'Pengguna & Hak Akses', 'description' => 'Akun dan hak akses', 'locked' => true, 'group' => 'system'],
        'menus' => ['path' => '/admin/settings/menus', 'permission' => 'manage-access', 'label' => 'Manajemen Menu', 'description' => 'Susunan navigasi Admin', 'locked' => true, 'group' => 'system'],
    ],
];
