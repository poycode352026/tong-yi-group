<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_superadmin')->default(false);
            $table->jsonb('permissions')->default('[]');
            $table->unsignedInteger('access_revision')->default(1);
        });
        // Pertahankan kemampuan Admin lama; tidak menaikkan siapa pun menjadi Superadmin.
        DB::table('users')->where('is_admin', true)->update(['permissions' => json_encode(['programs.view', 'programs.manage', 'programs.publish'])]);
        Schema::create('admin_menu_items', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('label', 80);
            $table->string('description', 160)->default('');
            $table->unsignedInteger('position');
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('revision')->default(1);
            $table->timestamp('updated_at');
        });
        foreach (['dashboard' => ['Dashboard', 'Ringkasan ruang kerja'], 'programs' => ['Program Akademik', 'Program dan kurikulum'], 'users' => ['Pengguna & Permission', 'Akun dan hak akses'], 'menus' => ['Manajemen Menu', 'Susunan navigasi Admin']] as $key => $labels) {
            DB::table('admin_menu_items')->insert(['key' => $key, 'label' => $labels[0], 'description' => $labels[1], 'position' => DB::table('admin_menu_items')->count() + 1, 'updated_at' => now()]);
        }
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('action');
            $table->jsonb('before');
            $table->jsonb('after');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
        Schema::dropIfExists('admin_menu_items');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_superadmin', 'permissions', 'access_revision']);
        });
    }
};
