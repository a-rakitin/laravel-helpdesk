<?php

namespace Tests\Feature\Ticket;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangeStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_change_ticket_status(): void
    {
        $agent = User::factory()->create(['role' => UserRole::AGENT]);

        $ticket = Ticket::create([
            'title' => 'Test',
            'description' => 'Desc',
            'created_by' => $agent->id,
            'status' => TicketStatus::OPEN,
        ]);

        $response = $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/tickets/{$ticket->id}/status", [
                'status' => 'in_progress',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'in_progress');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_customer_cannot_change_status(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $ticket = Ticket::create([
            'title' => 'Test',
            'description' => 'Desc',
            'created_by' => $customer->id,
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->patchJson("/api/tickets/{$ticket->id}/status", [
                'status' => 'closed',
            ]);

        $response->assertForbidden();
    }
}
