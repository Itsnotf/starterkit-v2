<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardService
{
    public function adminStats(): array
    {
        return [
            'total_users'       => User::count(),
            'total_roles'       => Role::count(),
            'total_permissions' => Permission::count(),
            'recent_users'      => User::with('roles')->latest()->take(5)->get(),
        ];
    }

    public function memberStats(User $user): array
    {
        return [
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'member_since' => $user->created_at->format('d M Y'),
        ];
    }
}
