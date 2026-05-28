<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest\CreateUserRequest;
use App\Http\Requests\UserRequest\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    public function __construct(private UserService $userService) {}

    public static function middleware()
    {
        return [
            new Middleware('permission:users index',  only: ['index']),
            new Middleware('permission:users create', only: ['create', 'store']),
            new Middleware('permission:users edit',   only: ['edit', 'update']),
            new Middleware('permission:users delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        return inertia('users/index', [
            'users'   => $this->userService->getAll($request->search),
            'filters' => $request->only('search'),
            'flash'   => ['success' => session('success')],
        ]);
    }

    public function create()
    {
        return inertia('users/create', [
            'roles' => $this->userService->getRoles(),
        ]);
    }

    public function store(CreateUserRequest $request)
    {
        $this->userService->create($request->validated(), $request->role);

        return redirect()->route('users.index')->with('success', 'users created successfully');
    }

    public function edit(string $id)
    {
        return inertia('users/edit', [
            'user'  => $this->userService->findById($id),
            'roles' => $this->userService->getRoles(),
        ]);
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        $this->userService->update($id, $request->validated(), $request->role);

        return redirect()->route('users.index')->with('success', 'users updated successfully');
    }

    public function destroy(string $id)
    {
        $this->userService->delete($id);

        return redirect()->route('users.index')->with('success', 'users deleted successfully');
    }
}
