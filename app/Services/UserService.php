<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

class UserService
{
    public function getAll(?string $search): LengthAwarePaginator
    {
        return User::with('roles')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(config('starterkit.pagination'))
            ->withQueryString();
    }

    public function getRoles()
    {
        return Role::all();
    }

    public function create(array $data, string $role): User
    {
        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => bcrypt($data['password']),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role);

        return $user;
    }

    public function findById(string $id): User
    {
        return User::with('roles')->findOrFail($id);
    }

    public function update(string $id, array $data, string $role): User
    {
        $user = $this->findById($id);
        $user->update($data);
        $user->syncRoles($role);

        return $user;
    }

    public function delete(string $id): void
    {
        $this->findById($id)->delete();
    }
}
