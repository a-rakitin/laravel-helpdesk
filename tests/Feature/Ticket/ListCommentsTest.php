<?php

namespace Tests\Feature\Ticket;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListCommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_view_ticket_comments(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $agent = User::factory()->create([
            'role' => UserRole::AGENT,
        ]);

        $ticket = Ticket::create([
            'title' => 'Ticket',
            'description' => 'Desc',
            'created_by' => $customer->id,
        ]);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $customer->id,
            'body' => 'Customer comment',
        ]);

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson("/api/tickets/{$ticket->id}/comments");

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.body', 'Customer comment');
    }
}
