<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('access-admin', fn (User $user): bool => $user->is_admin === true);
        Gate::define('manage-access', fn (User $user): bool => $user->is_admin === true && $user->is_superadmin === true);
        foreach (array_keys(config('admin.permissions')) as $permission) {
            Gate::define($permission, fn (User $user): bool => $user->hasAdminPermission($permission));
        }
    }
}
