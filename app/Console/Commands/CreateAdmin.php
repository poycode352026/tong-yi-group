<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\ActivityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create';

    protected $description = 'Membuat akun Admin melalui isian interaktif tanpa kata sandi bawaan';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Jalankan perintah ini secara interaktif untuk mengisi kata sandi tersembunyi.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Nama Admin'));
        $email = Str::lower(trim((string) $this->ask('Email Admin')));
        $identity = Validator::make(compact('name', 'email'), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Alamat email tidak valid.',
            'email.max' => 'Alamat email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan. Akun yang ada tidak diubah.',
        ]);

        if ($identity->fails()) {
            $this->error($identity->errors()->first());

            return self::FAILURE;
        }

        $password = $this->secret('Kata sandi (minimal 12 karakter, maksimal 72 byte)');
        $password_confirmation = $this->secret('Ulangi kata sandi');
        $credentials = Validator::make(compact('password', 'password_confirmation'), [
            'password' => ['required', 'string', 'confirmed', Password::min(12), function ($attribute, $value, $fail) {
                if (strlen($value) > 72) {
                    $fail('Kata sandi maksimal 72 byte.');
                }
            }],
        ], [
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai.',
            'password.min' => 'Kata sandi minimal 12 karakter.',
        ]);

        if ($credentials->fails()) {
            $this->error($credentials->errors()->first());

            return self::FAILURE;
        }

        DB::transaction(function () use ($name, $email, $password) {
            $admin = new User(compact('name', 'email', 'password'));
            $admin->is_admin = true;
            $admin->save();
            ActivityLog::record(null, 'user.created.cli', 'Akun Admin dibuat melalui terminal', [], ['name' => $admin->name, 'email' => $admin->email, 'user_id' => $admin->id], 'user', (string) $admin->id);
        });

        $this->info('Akun Admin berhasil dibuat. Silakan masuk melalui /admin/login.');

        return self::SUCCESS;
    }
}
