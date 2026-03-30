<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'qa-admin@example.com',
        ], [
            'name' => 'QA Admin',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
        ]);

        User::updateOrCreate([
            'email' => 'qa-agent@example.com',
        ], [
            'name' => 'QA Agent',
            'password' => Hash::make('password'),
            'role' => UserRole::AGENT,
        ]);

        User::factory()->count(3)->create([
            'role' => UserRole::CUSTOMER,
        ]);
    }
}
