<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_audit_logs', function (Blueprint $table) {
            $table->string('source_key')->nullable()->unique();
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->string('summary')->nullable();
            $table->string('outcome', 20)->default('success');
            $table->index(['created_at', 'id']);
        });
        $position = (int) DB::table('admin_menu_items')->max('position');
        foreach (['activity' => ['Riwayat Aktivitas', 'Perubahan dan aktivitas sistem'], 'archives' => ['Arsip Level', 'Level yang dikeluarkan dari program']] as $key => [$label, $description]) {
            DB::table('admin_menu_items')->insert(['key' => $key, 'label' => $label, 'description' => $description, 'position' => ++$position, 'is_visible' => true, 'revision' => 1, 'updated_at' => now()]);
        }
        // Catatan historis memakai waktu/aktor asli, bukan waktu migrasi.
        DB::statement(<<<'SQL'
            INSERT INTO admin_audit_logs (actor_id, action, source_key, subject_type, subject_id, summary, "before", "after", created_at)
            SELECT actor_id, CASE WHEN action = 'publish' THEN 'program.published' ELSE 'program.draft.saved' END,
                'program:' || id, 'program', program_id::text, 'Perubahan program, revisi ' || revision,
                COALESCE(LAG(content) OVER (PARTITION BY program_id ORDER BY revision), '{}'::jsonb), content, created_at
            FROM program_revisions
        SQL);
        DB::statement(<<<'SQL'
            INSERT INTO admin_audit_logs (actor_id, action, source_key, subject_type, subject_id, summary, "before", "after", created_at)
            SELECT v.actor_id, 'learning.version.saved', 'learning:' || v.id, r.kind, r.id::text,
                'Versi sumber belajar: ' || v.title, '{}'::jsonb,
                jsonb_build_object('title', v.title, 'status', v.status, 'revision', v.revision, 'version_id', v.id,
                    'levels', v.content->'levels', 'scope', v.content->'scope', 'usage', v.content->'usage', 'file_name', v.file_name,
                    'questions_count', jsonb_array_length(COALESCE(v.content->'questions', '[]'::jsonb))), v.created_at
            FROM learning_resource_versions v JOIN learning_resources r ON r.id = v.resource_id
        SQL);
    }

    public function down(): void
    {
        DB::table('admin_menu_items')->whereIn('key', ['activity', 'archives'])->delete();
        DB::table('admin_audit_logs')->whereNotNull('source_key')->delete();
        Schema::table('admin_audit_logs', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'id']);
            $table->dropUnique(['source_key']);
            $table->dropColumn(['source_key', 'subject_type', 'subject_id', 'summary', 'outcome']);
        });
    }
};
