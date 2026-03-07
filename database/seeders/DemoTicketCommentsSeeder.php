<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoTicketCommentsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        Ticket::all()->each(function (Ticket $ticket) use ($users) {
            TicketComment::factory()
                ->count(rand(1, 4))
                ->create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $users->random()->id,
                ]);
        });
    }
}
