<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminNavigation
{
    public static function forUser(?User $user): array
    {
        if (! $user?->can('access-admin')) {
            return [];
        }
        $registry = config('admin.menus');

        return DB::table('admin_menu_items')->orderBy('position')->orderBy('key')->get()
            ->filter(fn ($item) => isset($registry[$item->key]) && $user->can($registry[$item->key]['permission']) && ($item->is_visible || $registry[$item->key]['locked']))
            ->map(fn ($item) => ['key' => $item->key, 'label' => $item->label, 'description' => $item->description, 'path' => $registry[$item->key]['path'], 'hidden' => $registry[$item->key]['hidden'] ?? false, 'group' => $registry[$item->key]['group'] ?? 'workspace'])
            ->sortBy(fn ($item) => ['workspace' => 0, 'system' => 1][$item['group']])
            ->values()->all();
    }
}
