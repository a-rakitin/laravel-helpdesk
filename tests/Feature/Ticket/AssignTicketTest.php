<?php

namespace Tests\Feature\Ticket;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_assign_ticket_to_another_agent(): void
    {
        $agent = User::factory()->create(['role' => UserRole::AGENT]);
        $assignee = User::factory()->create(['role' => UserRole::AGENT]);

        $ticket = Ticket::create([
            'title' => 'Test',
            'description' => 'Desc',
            'created_by' => $agent->id,
        ]);

        $response = $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/tickets/{$ticket->id}/assign", [
                'assigned_to' => $assignee->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('assigned_to', $assignee->id);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $assignee->id,
        ]);
    }

    public function test_admin_can_assign_ticket_to_agent(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $assignee = User::factory()->create(['role' => UserRole::AGENT]);

        $ticket = Ticket::create([
            'title' => 'Test',
            'description' => 'Desc',
            'created_by' => $customer->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/tickets/{$ticket->id}/assign", [
                'assigned_to' => $assignee->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('assigned_to', $assignee->id);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $assignee->id,
        ]);
    }

    public function test_customer_cannot_assign_ticket(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $agent = User::factory()->create(['role' => UserRole::AGENT]);

        $ticket = Ticket::create([
            'title' => 'Test',
            'description' => 'Desc',
            'created_by' => $customer->id,
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->patchJson("/api/tickets/{$ticket->id}/assign", [
                'assigned_to' => $agent->id,
            ]);

        $response->assertForbidden();
    }

    public function test_agent_cannot_assign_ticket_to_customer(): void
    {
        $agent = User::factory()->create(['role' => UserRole::AGENT]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $ticket = Ticket::create([
            'title' => 'Test',
            'description' => 'Desc',
            'created_by' => $customer->id,
        ]);

        $response = $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/tickets/{$ticket->id}/assign", [
                'assigned_to' => $customer->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Ticket can be assigned only to an agent.');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => null,
        ]);
    }

    public function test_admin_cannot_assign_ticket_to_customer(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $ticket = Ticket::create([
            'title' => 'Test',
            'description' => 'Desc',
            'created_by' => $customer->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/tickets/{$ticket->id}/assign", [
                'assigned_to' => $customer->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Ticket can be assigned only to an agent.');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => null,
        ]);
    }
}
