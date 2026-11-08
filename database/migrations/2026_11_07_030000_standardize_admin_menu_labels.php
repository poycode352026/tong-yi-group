<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['materials' => ['Library Materi', 'Modul'], 'users' => ['Pengguna & Permission', 'Pengguna & Hak Akses'], 'dashboard' => ['Dashboard', 'Dasbor']] as $key => [$before, $after]) {
            DB::table('admin_menu_items')->where('key', $key)->where('label', $before)
                ->update(['label' => $after, 'revision' => DB::raw('revision + 1'), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        foreach (['materials' => ['Modul', 'Library Materi'], 'users' => ['Pengguna & Hak Akses', 'Pengguna & Permission'], 'dashboard' => ['Dasbor', 'Dashboard']] as $key => [$before, $after]) {
            DB::table('admin_menu_items')->where('key', $key)->where('label', $before)
                ->update(['label' => $after, 'revision' => DB::raw('revision + 1'), 'updated_at' => now()]);
        }
    }
};
