<?php

namespace Tests\Feature\Ticket;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_own_ticket(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $ticket = Ticket::create([
            'title' => 'Own ticket',
            'description' => 'Owned by customer',
            'created_by' => $customer->id,
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson("/api/tickets/{$ticket->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $ticket->id);
    }

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

    public function test_agent_can_view_customer_ticket(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $agent = User::factory()->create([
            'role' => UserRole::AGENT,
        ]);

        $ticket = Ticket::create([
            'title' => 'Customer ticket',
            'description' => 'Visible to support',
            'created_by' => $customer->id,
        ]);

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson("/api/tickets/{$ticket->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $ticket->id);
    }

    public function test_admin_can_view_customer_ticket(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $ticket = Ticket::create([
            'title' => 'Customer ticket',
            'description' => 'Visible to admins',
            'created_by' => $customer->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/tickets/{$ticket->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $ticket->id);
    }
}
