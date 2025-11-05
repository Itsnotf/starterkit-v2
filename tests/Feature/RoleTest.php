<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test permissions
    Permission::create(['name' => 'roles index']);
    Permission::create(['name' => 'roles create']);
    Permission::create(['name' => 'roles edit']);
    Permission::create(['name' => 'roles delete']);
});

test('unauthorized users cannot access roles index', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/roles');
    $response->assertStatus(403);
});

test('authorized user can view roles index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('roles index');
    
    $response = $this->actingAs($user)->get('/roles');
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('roles/index')
            ->has('roles')
            ->has('filters')
            ->has('flash'));
});

test('authorized user can search roles', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('roles index');
    
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'editor']);
    
    $response = $this->actingAs($user)
        ->get('/roles?search=admin');
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('roles/index')
            ->where('filters.search', 'admin')
            ->has('roles.data', 1)
            ->has('flash'));
});

test('authorized user can view create role form', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('roles create');
    
    $response = $this->actingAs($user)->get('/roles/create');
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('roles/create')
            ->has('permissions'));
});

test('authorized user can create new role with permissions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('roles create');
    
    $permissions = Permission::all();
    
    $response = $this->actingAs($user)
        ->post('/roles', [
            'name' => 'new-role',
            'permissions' => $permissions->pluck('name')->toArray(),
        ]);
    
    $response->assertRedirect('/roles')
        ->assertSessionHas('success');
        
    $this->assertDatabaseHas('roles', ['name' => 'new-role']);
    
    $role = Role::where('name', 'new-role')->first();
    expect($role->permissions)->toHaveCount(4);
});

test('authorized user can view edit role form', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('roles edit');
    
    $role = Role::create(['name' => 'test-role']);
    
    $response = $this->actingAs($user)->get("/roles/{$role->id}/edit");
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('roles/edit')
            ->has('role')
            ->has('permissions'));
});

test('authorized user can update role and its permissions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('roles edit');
    
    $role = Role::create(['name' => 'old-role']);
    $permissions = Permission::all();
    
    $response = $this->actingAs($user)
        ->put("/roles/{$role->id}", [
            'name' => 'updated-role',
            'permissions' => $permissions->pluck('name')->toArray(),
        ]);
    
    $response->assertRedirect('/roles')
        ->assertSessionHas('success');
        
    $this->assertDatabaseHas('roles', ['name' => 'updated-role']);
    
    $updatedRole = Role::find($role->id);
    expect($updatedRole->permissions)->toHaveCount(4);
});

test('authorized user can delete role', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('roles delete');
    
    $role = Role::create(['name' => 'to-delete']);
    
    $response = $this->actingAs($user)
        ->delete("/roles/{$role->id}");
    
    $response->assertRedirect('/roles')
        ->assertSessionHas('success');
        
    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

test('cannot create role with duplicate name', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('roles create');
    
    Role::create(['name' => 'existing-role']);
    
    $response = $this->actingAs($user)
        ->post('/roles', [
            'name' => 'existing-role',
            'permissions' => [],
        ]);
    
    $response->assertSessionHasErrors(['name']);
});

test('cannot update role with duplicate name', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('roles edit');
    
    Role::create(['name' => 'existing-role']);
    $roleToUpdate = Role::create(['name' => 'to-update']);
    
    $response = $this->actingAs($user)
        ->put("/roles/{$roleToUpdate->id}", [
            'name' => 'existing-role',
            'permissions' => [],
        ]);
    
    $response->assertSessionHasErrors(['name']);
});