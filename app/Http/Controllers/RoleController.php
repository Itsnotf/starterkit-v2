<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest\CreateRoleRequest;
use App\Http\Requests\RoleRequest\UpdateRoleRequest;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoleController extends Controller implements HasMiddleware
{
    public function __construct(private RoleService $roleService) {}

    public static function middleware()
    {
        return [
            new Middleware('permission:roles index',  only: ['index']),
            new Middleware('permission:roles create', only: ['create', 'store']),
            new Middleware('permission:roles edit',   only: ['edit', 'update']),
            new Middleware('permission:roles delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        return inertia('roles/index', [
            'roles'   => $this->roleService->getAll($request->search),
            'filters' => $request->only('search'),
            'flash'   => ['success' => session('success')],
        ]);
    }

    public function create()
    {
        return inertia('roles/create', [
            'permissions' => $this->roleService->getPermissions(),
        ]);
    }

    public function store(CreateRoleRequest $request)
    {
        $this->roleService->create($request->name, $request->permissions);

        return redirect()->route('roles.index')->with('success', 'roles created successfully');
    }

    public function edit(string $id)
    {
        return inertia('roles/edit', [
            'role'        => $this->roleService->findById($id),
            'permissions' => $this->roleService->getPermissions(),
        ]);
    }

    public function update(UpdateRoleRequest $request, string $id)
    {
        $this->roleService->update($id, $request->name, $request->permissions);

        return redirect()->route('roles.index')->with('success', 'roles updated successfully');
    }

    public function destroy(string $id)
    {
        $this->roleService->delete($id);

        return redirect()->route('roles.index')->with('success', 'roles deleted successfully');
    }
}
