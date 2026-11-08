<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ContentComparison;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AccessSettingsController extends Controller
{
    public function users(Request $request): Response
    {
        $search = $request->validate(['search' => ['nullable', 'string', 'max:100']])['search'] ?? '';
        $query = User::query()->select(['id', 'name', 'email', 'is_admin', 'is_superadmin', 'permissions', 'access_revision'])->orderBy('id');
        if ($search !== '') {
            $query->where(fn ($query) => $query->where('name', 'ilike', '%'.$search.'%')->orWhere('email', 'ilike', '%'.$search.'%'));
        }

        return Inertia::render('Admin/Settings/Users', ['users' => $query->paginate(20)->withQueryString(), 'search' => $search, 'permissions' => config('admin.permissions'), 'actorId' => $request->user()->id]);
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'is_admin' => ['required', 'boolean'],
            'is_superadmin' => ['required', 'boolean'],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', 'distinct', Rule::in(array_keys(config('admin.permissions')))],
            'access_revision' => ['required', 'integer', 'min:1'],
        ], ['required' => 'Lengkapi isian akses.', 'boolean' => 'Pilihan akses tidak valid.', 'in' => 'Permission tidak dikenali.', 'distinct' => 'Permission tidak boleh berulang.']);
        if ($data['is_superadmin'] && ! $data['is_admin']) {
            throw ValidationException::withMessages(['is_admin' => 'Superadmin harus memiliki akses Admin.']);
        }
        $permissions = $data['permissions'];
        if ((in_array('programs.manage', $permissions) && ! in_array('programs.view', $permissions)) || (in_array('programs.publish', $permissions) && ! in_array('programs.manage', $permissions))) {
            throw ValidationException::withMessages(['permissions' => 'Izin mengubah memerlukan izin melihat; izin menerbitkan memerlukan izin mengubah.']);
        }
        if (! $data['is_admin'] && $permissions !== []) {
            throw ValidationException::withMessages(['permissions' => 'Akun tanpa akses Admin tidak boleh memiliki permission Admin.']);
        }
        DB::transaction(function () use ($request, $user, $data) {
            // Serialisasi perubahan kewenangan agar pemeriksaan Superadmin terakhir aman lintas tab.
            DB::select('SELECT pg_advisory_xact_lock(?)', [740071]);
            abort_unless(User::find($request->user()->id)?->can('manage-access'), 403);
            $target = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($target->access_revision !== (int) $data['access_revision']) {
                throw ValidationException::withMessages(['access_revision' => 'Akses akun telah berubah. Muat ulang halaman sebelum menyimpan kembali.']);
            }
            if ($target->id === $request->user()->id && (! $data['is_admin'] || ! $data['is_superadmin'])) {
                throw ValidationException::withMessages(['is_superadmin' => 'Anda tidak dapat mencabut akses Superadmin sendiri.']);
            }
            if ($target->is_superadmin && (! $data['is_admin'] || ! $data['is_superadmin']) && User::where('is_admin', true)->where('is_superadmin', true)->where('id', '!=', $target->id)->count() === 0) {
                throw ValidationException::withMessages(['is_superadmin' => 'Minimal satu Superadmin harus tetap tersedia.']);
            }
            $before = $target->only('is_admin', 'is_superadmin', 'permissions');
            $target->is_admin = $data['is_admin'];
            $target->is_superadmin = $data['is_superadmin'];
            $target->permissions = array_values($data['permissions']);
            $oldPermissions = $before['permissions'] ?? [];
            $newPermissions = $target->permissions ?? [];
            sort($oldPermissions);
            sort($newPermissions);
            if ($before['is_admin'] === $target->is_admin && $before['is_superadmin'] === $target->is_superadmin && $oldPermissions === $newPermissions) {
                return;
            }
            $target->access_revision++;
            $target->save();
            $this->audit($request->user()->id, $target->id, 'user.access.updated', $before, $target->only('is_admin', 'is_superadmin', 'permissions'));
        });

        return back()->with('success', 'Akses pengguna berhasil diperbarui.');
    }

    public function menus(): Response
    {
        return Inertia::render('Admin/Settings/Menus', ['menus' => DB::table('admin_menu_items')->orderBy('position')->orderBy('key')->get()->map(fn ($item) => [...(array) $item, 'locked' => config('admin.menus.'.$item->key.'.locked', true)])]);
    }

    public function updateMenus(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'menus' => ['required', 'array', 'size:'.count(config('admin.menus'))],
            'menus.*' => ['array:key,label,description,position,is_visible,revision,locked,updated_at'],
            'menus.*.key' => ['required', 'distinct', Rule::in(array_keys(config('admin.menus')))],
            'menus.*.label' => ['required', 'string', 'max:80'],
            'menus.*.description' => ['nullable', 'string', 'max:160'],
            'menus.*.position' => ['required', 'integer', 'min:1', 'max:999'],
            'menus.*.is_visible' => ['required', 'boolean'],
            'menus.*.revision' => ['required', 'integer', 'min:1'],
        ], ['required' => 'Lengkapi isian menu.', 'distinct' => 'Menu tidak boleh berulang.', 'in' => 'Menu tidak dikenali.', 'max' => 'Isian menu terlalu panjang atau melewati batas teknis.']);
        foreach ($data['menus'] as $index => $menu) {
            if (config('admin.menus.'.$menu['key'].'.locked') && ! $menu['is_visible']) {
                throw ValidationException::withMessages(["menus.$index.is_visible" => 'Menu ini selalu ditampilkan agar akses administrasi tetap tersedia.']);
            }
        }
        DB::transaction(function () use ($request, $data) {
            DB::select('SELECT pg_advisory_xact_lock(?)', [740071]);
            abort_unless(User::find($request->user()->id)?->can('manage-access'), 403);
            $before = DB::table('admin_menu_items')->orderBy('key')->lockForUpdate()->get()->keyBy('key');
            foreach ($data['menus'] as $menu) {
                if ($before[$menu['key']]->revision !== (int) $menu['revision']) {
                    throw ValidationException::withMessages(['menus' => 'Susunan menu sudah berubah. Muat ulang halaman sebelum menyimpan kembali.']);
                }
                $existing = $before[$menu['key']];
                if ($existing->label === $menu['label'] && ($existing->description ?? '') === ($menu['description'] ?? '')
                    && $existing->position === (int) $menu['position'] && $existing->is_visible === (bool) $menu['is_visible']) {
                    continue;
                }
                DB::table('admin_menu_items')->where('key', $menu['key'])->update(['label' => $menu['label'], 'description' => $menu['description'] ?? '', 'position' => $menu['position'], 'is_visible' => $menu['is_visible'], 'revision' => $menu['revision'] + 1, 'updated_at' => now()]);
            }
            $after = DB::table('admin_menu_items')->orderBy('key')->get()->toArray();
            if (! ContentComparison::same(json_decode(json_encode($before->values()), true), json_decode(json_encode($after), true))) {
                $this->audit($request->user()->id, null, 'menus.updated', $before->values()->toArray(), $after);
            }
        });

        return back()->with('success', 'Susunan menu berhasil disimpan.');
    }

    private function audit(int $actor, ?int $target, string $action, array $before, array $after): void
    {
        DB::table('admin_audit_logs')->insert(['actor_id' => $actor, 'target_user_id' => $target, 'action' => $action, 'before' => json_encode($before, JSON_THROW_ON_ERROR), 'after' => json_encode($after, JSON_THROW_ON_ERROR), 'created_at' => now()]);
    }
}
