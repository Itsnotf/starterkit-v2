<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user']);
        User::factory(10)->create()->each(function ($user) {
            $user->assignRole('user');
        });
    }
}
