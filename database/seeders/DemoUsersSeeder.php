<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
        ]);

        User::create([
            'name' => 'Agent One',
            'email' => 'agent1@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::AGENT,
        ]);

        User::create([
            'name' => 'Agent Two',
            'email' => 'agent2@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::AGENT,
        ]);

        User::factory()->count(3)->create([
            'role' => UserRole::CUSTOMER,
        ]);
    }
}
