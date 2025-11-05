<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest\CreateUserRequest;
use App\Http\Requests\UserRequest\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Spatie\Permission\Models\Role as ModelsRole;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('permission:users index', only: ['index']),
            new Middleware('permission:users create', only: ['create', 'store']),
            new Middleware('permission:users edit', only: ['edit', 'update   ']),
            new Middleware('permission:users delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $users = User::with('roles')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(8)
            ->withQueryString();

        return inertia('users/index', [
            'users' => $users,
            'filters' => $request->only('search'),
            'flash' => [
                'success' => session('success'),
            ],
        ]);
    }


    public function create()
    {
        $roles = ModelsRole::all();
        return Inertia::render("users/create", [
            "roles" => $roles
        ]);
    }

    public function store(CreateUserRequest $request)
    {
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($request->role);

        return redirect()->route("users.index")->with("success", "users created successfully");
    }


    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = ModelsRole::all();
        return Inertia::render("users/edit", [
            "user" => $user,
            "roles" => $roles
        ]);
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());

        $user->syncRoles($request->role);

        return redirect()->route("users.index")->with("success", "users updated successfully");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route("users.index")->with("success", "users deleted successfully");
    }
}
