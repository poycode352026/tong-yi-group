<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kind', 20);
            $table->unsignedInteger('revision');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
        Schema::create('learning_resource_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('resource_id')->constrained('learning_resources')->restrictOnDelete();
            $table->unsignedInteger('revision');
            $table->string('title');
            $table->string('status', 20);
            $table->jsonb('content');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at');
            $table->unique(['resource_id', 'revision']);
        });
        // Tambahkan tanpa mengubah susunan/label menu yang sudah diatur Superadmin.
        $position = (int) DB::table('admin_menu_items')->max('position');
        foreach (['frameworks' => ['Acuan Pembelajaran', 'Standar dan cakupan materi'], 'materials' => ['Library Materi', 'PDF dan tautan referensi'], 'assessments' => ['Latihan Resmi', 'Soal per paket dan akhir level']] as $key => [$label, $description]) {
            DB::table('admin_menu_items')->insert(['key' => $key, 'label' => $label, 'description' => $description, 'position' => ++$position, 'is_visible' => true, 'revision' => 1, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        DB::table('admin_menu_items')->whereIn('key', ['frameworks', 'materials', 'assessments'])->delete();
        Schema::dropIfExists('learning_resource_versions');
        Schema::dropIfExists('learning_resources');
    }
};
