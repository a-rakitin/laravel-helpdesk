<?php

namespace Tests\Feature\Ticket;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_view_another_users_ticket(): void
    {
        $owner = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $other = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $ticket = Ticket::create([
            'title' => 'Secret ticket',
            'description' => 'Secret',
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($other, 'sanctum')
            ->getJson("/api/tickets/{$ticket->id}");

        $response->assertForbidden();
    }
}
