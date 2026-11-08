<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('admin_menu_items')->where('key', 'assessments')->where('label', 'Latihan Resmi')
            ->update(['label' => 'Latihan', 'revision' => DB::raw('revision + 1'), 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('admin_menu_items')->where('key', 'assessments')->where('label', 'Latihan')
            ->update(['label' => 'Latihan Resmi', 'revision' => DB::raw('revision + 1'), 'updated_at' => now()]);
    }
};
