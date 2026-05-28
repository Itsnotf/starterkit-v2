<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function getAll(?string $search): LengthAwarePaginator
    {
        return Role::with('permissions')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate(config('starterkit.pagination'))
            ->withQueryString();
    }

    public function getPermissions()
    {
        $labels = config('starterkit.permissions');

        return Permission::all()->map(function ($permission) use ($labels) {
            $permission->label = $labels[$permission->name] ?? $permission->name;
            return $permission;
        });
    }

    public function create(string $name, ?array $permissions): Role
    {
        $role = Role::create([
            'name'       => $name,
            'guard_name' => 'web',
        ]);

        $role->givePermissionTo($permissions);

        return $role;
    }

    public function findById(string $id): Role
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function update(string $id, string $name, ?array $permissions): Role
    {
        $role = Role::findOrFail($id);
        $role->update(['name' => $name]);
        $role->syncPermissions($permissions);

        return $role;
    }

    public function delete(string $id): void
    {
        Role::findOrFail($id)->delete();
    }
}
