<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest\CreateRoleRequest;
use App\Http\Requests\RoleRequest\UpdateRoleRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('permission:roles index', only: ['index']),
            new Middleware('permission:roles create', only: ['create', 'store']),
            new Middleware('permission:roles edit', only: ['edit', 'update   ']),
            new Middleware('permission:roles delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $roles = Role::with('permissions')->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate(8)
            ->withQueryString();

        return inertia('roles/index', [
            'roles' => $roles,
            'filters' => $request->only('search'),
            'flash' => [
                'success' => session('success'),
            ],
        ]);
    }


    public function create()
    {
        $permissions = Permission::all();
        return Inertia::render("roles/create", [
            "permissions" => $permissions
        ]);
    }

    public function store(CreateRoleRequest $request)
    {
        $role = Role::create([
            "name" => $request->name,
            "guard_name" => 'web',
        ]);

        $role->givePermissionTo($request->permissions);

        return redirect()->route("roles.index")->with("success", "roles created successfully");
    }


    public function edit(string $id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all();
        return Inertia::render("roles/edit", [
            "role" => $role,
            "permissions" => $permissions
        ]);
    }

    public function update(UpdateRoleRequest $request, string $id)
    {
        $role = Role::findOrFail($id);
        $role->update($request->all());

        $role->syncPermissions($request->permissions);

        return redirect()->route("roles.index")->with("success", "roles updated successfully");
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return redirect()->route("roles.index")->with("success", "roles deleted successfully");
    }
}
