<?php

namespace Tests\Feature\Ticket;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTicketsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_sees_only_own_tickets_and_can_filter_by_status(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $otherCustomer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        // customer tickets
        Ticket::create([
            'title' => 'My open',
            'description' => 'A',
            'status' => TicketStatus::OPEN,
            'created_by' => $customer->id,
        ]);

        Ticket::create([
            'title' => 'My closed',
            'description' => 'B',
            'status' => TicketStatus::CLOSED,
            'created_by' => $customer->id,
        ]);

        // other customer ticket
        Ticket::create([
            'title' => 'Other open',
            'description' => 'C',
            'status' => TicketStatus::OPEN,
            'created_by' => $otherCustomer->id,
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/tickets?status=open&per_page=100');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 100);

        $titles = collect($response->json('data'))->pluck('title')->all();

        $this->assertContains('My open', $titles);
        $this->assertNotContains('My closed', $titles);
        $this->assertNotContains('Other open', $titles);
    }

    public function test_agent_can_see_customer_tickets(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $agent = User::factory()->create([
            'role' => UserRole::AGENT,
        ]);

        $ticket = Ticket::factory()->create([
            'title' => 'Customer ticket',
            'created_by' => $customer->id,
        ]);

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson('/api/tickets?per_page=100');

        $response->assertOk();

        $ticketIds = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($ticket->id, $ticketIds);
    }

    public function test_admin_can_see_customer_tickets(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $ticket = Ticket::factory()->create([
            'title' => 'Customer ticket',
            'created_by' => $customer->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/tickets?per_page=100');

        $response->assertOk();

        $ticketIds = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($ticket->id, $ticketIds);
    }
}
