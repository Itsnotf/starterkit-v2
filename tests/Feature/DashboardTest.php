<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'user']);
});

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('admin sees admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->component('dashboard/admin')
            ->has('total_users')
            ->has('total_roles')
            ->has('total_permissions')
            ->has('recent_users')
        );
});

test('member sees member dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->component('dashboard/member')
            ->has('roles')
            ->has('permissions')
            ->has('member_since')
        );
});
