<?php

namespace Database\Seeders;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoTicketsSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', UserRole::CUSTOMER)->get();
        $agents = User::where('role', UserRole::AGENT)->get();

        if ($customers->isEmpty()) {
            return;
        }

        foreach ($customers as $customer) {
            Ticket::factory()->count(3)->create([
                'created_by' => $customer->id,
                'assigned_to' => $agents->random()?->id,
                'status' => fake()->randomElement([
                    TicketStatus::OPEN,
                    TicketStatus::IN_PROGRESS,
                    TicketStatus::CLOSED,
                ]),
                'priority' => fake()->randomElement([
                    TicketPriority::LOW,
                    TicketPriority::MEDIUM,
                    TicketPriority::HIGH,
                ]),
            ]);
        }
    }
}
