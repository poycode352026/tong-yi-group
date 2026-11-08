<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GrantSuperadmin extends Command
{
    protected $signature = 'admin:superadmin {email : Email akun Admin yang akan menjadi Superadmin}';

    protected $description = 'Memberikan akses Superadmin kepada akun Admin yang dipilih melalui terminal';

    public function handle(): int
    {
        $email = mb_strtolower(trim($this->argument('email')));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user || ! $user->is_admin) {
            $this->error('Akun Admin tidak ditemukan. Akun tidak diubah.');

            return self::FAILURE;
        }
        DB::transaction(function () use ($user) {
            DB::select('SELECT pg_advisory_xact_lock(?)', [740071]);
            $target = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $before = $target->only('is_admin', 'is_superadmin', 'permissions');
            $target->is_superadmin = true;
            $target->access_revision++;
            $target->save();
            DB::table('admin_audit_logs')->insert(['actor_id' => null, 'target_user_id' => $target->id, 'action' => 'superadmin.granted.cli', 'before' => json_encode($before), 'after' => json_encode($target->only('is_admin', 'is_superadmin', 'permissions')), 'created_at' => now()]);
        });
        $this->info('Akses Superadmin berhasil diberikan. Muat ulang ruang Admin.');

        return self::SUCCESS;
    }
}
