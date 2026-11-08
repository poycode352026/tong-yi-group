<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('admin_menu_items')->where('key', 'materials')->whereIn('label', ['Pustaka Guru', 'Library Materi', 'Modul'])->where('label', '!=', 'Modul')
            ->update(['label' => 'Modul', 'revision' => DB::raw('revision + 1'), 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('admin_menu_items')->where('key', 'materials')->where('label', 'Modul')->update(['label' => 'Pustaka Guru', 'revision' => DB::raw('revision + 1'), 'updated_at' => now()]);
    }
};
