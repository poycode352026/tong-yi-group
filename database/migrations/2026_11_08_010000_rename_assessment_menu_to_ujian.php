<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('admin_menu_items')->where('key', 'assessments')->whereIn('label', ['Latihan', 'Latihan Resmi'])
            ->update(['label' => 'Ujian', 'revision' => DB::raw('revision + 1'), 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('admin_menu_items')->where('key', 'assessments')->where('label', 'Ujian')
            ->update(['label' => 'Latihan', 'revision' => DB::raw('revision + 1'), 'updated_at' => now()]);
    }
};
