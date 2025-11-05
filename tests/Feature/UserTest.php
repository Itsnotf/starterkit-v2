<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::create(['name' => 'users index']);
    Permission::create(['name' => 'users create']);
    Permission::create(['name' => 'users edit']);
    Permission::create(['name' => 'users delete']);
});

test('index displays users', function () {
    $auth = User::factory()->create(['email_verified_at' => now()]);
    $auth->givePermissionTo('users index');

    $this->actingAs($auth)
        ->get('/users')
        ->assertStatus(200);
});

test('create shows form with roles', function () {
    $auth = User::factory()->create(['email_verified_at' => now()]);
    $auth->givePermissionTo('users create');
    Role::create(['name' => 'admin']);

    $this->actingAs($auth)
        ->get('/users/create')
        ->assertStatus(200);
});

test('store creates a user and assigns role', function () {
    $auth = User::factory()->create(['email_verified_at' => now()]);
    $auth->givePermissionTo('users create');
    Role::create(['name' => 'admin']);

    $response = $this->actingAs($auth)->post(route('users.store'), [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'admin',
    ]);

    $response->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('admin'))->toBeTrue();
});

test('edit shows edit form for a user', function () {
    $auth = User::factory()->create(['email_verified_at' => now()]);
    $auth->givePermissionTo('users edit');
    $user = User::factory()->create();

    $this->actingAs($auth)
        ->get(route('users.edit', $user->id))
        ->assertStatus(200);
});

test('update modifies user data', function () {
    $auth = User::factory()->create(['email_verified_at' => now()]);
    $auth->givePermissionTo('users edit');
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    // ensure role exists so validation passes
    Role::create(['name' => 'user']);

    $response = $this->actingAs($auth)->put(route('users.update', $user->id), [
        'name' => 'New Name',
        'email' => 'newemail@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'user',
    ]);

    // controller redirects back to index
    $response->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', ['email' => 'newemail@example.com', 'name' => 'New Name']);
});

test('destroy deletes a user', function () {
    // Create and authorize user with delete permission
    $auth = User::factory()->create(['email_verified_at' => now()]);
    $auth->givePermissionTo('users delete');
    
    $user = User::factory()->create(['email' => 'todelete@example.com']);

    $response = $this->actingAs($auth)->delete(route('users.destroy', $user->id));

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseMissing('users', ['email' => 'todelete@example.com']);
});
