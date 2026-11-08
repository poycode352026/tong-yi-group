<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Masukkan alamat email Anda.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.string' => 'Masukkan alamat email yang valid.',
            'email.max' => 'Alamat email terlalu panjang.',
            'password.required' => 'Masukkan kata sandi Anda.',
            'password.string' => 'Masukkan kata sandi yang valid.',
            'password.max' => 'Kata sandi terlalu panjang.',
        ];
    }

    public function authenticate(): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            event(new Lockout($this));

            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan masuk. Coba lagi dalam '.RateLimiter::availableIn($this->throttleKey()).' detik.',
            ]);
        }

        if (! Auth::attempt([...$this->only('email', 'password'), 'is_admin' => true])) {
            RateLimiter::hit($this->throttleKey(), 60);

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai, atau akun tidak memiliki akses Admin.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    private function throttleKey(): string
    {
        return 'admin-login:'.hash('sha256', $this->string('email').'|'.$this->ip());
    }
}
