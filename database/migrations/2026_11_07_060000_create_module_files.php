<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_resource_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('resource_id')->constrained('learning_resources')->restrictOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_resource_files');
    }
};
