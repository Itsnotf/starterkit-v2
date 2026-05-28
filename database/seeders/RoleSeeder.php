<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('starterkit.roles') as $role) {
            \Spatie\Permission\Models\Role::create(['name' => $role]);
        }

        $admin = \Spatie\Permission\Models\Role::findByName(config('starterkit.default_admin_role'));
        $admin->givePermissionTo(\Spatie\Permission\Models\Permission::all());
    }
}
