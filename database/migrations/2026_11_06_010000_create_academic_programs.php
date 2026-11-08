<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->jsonb('draft');
            $table->jsonb('published')->nullable();
            $table->unsignedInteger('revision')->default(1);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
        Schema::create('program_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('program_id')->constrained('academic_programs')->restrictOnDelete();
            $table->unsignedInteger('revision');
            $table->string('action');
            $table->jsonb('content');
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at');
            $table->unique(['program_id', 'revision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_revisions');
        Schema::dropIfExists('academic_programs');
    }
};
