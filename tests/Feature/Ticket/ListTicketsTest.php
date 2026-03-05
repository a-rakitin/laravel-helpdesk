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
}
