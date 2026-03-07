<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
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
            'created_by' => User::factory(),
            'assigned_to' => null,
        ];
    }
}
