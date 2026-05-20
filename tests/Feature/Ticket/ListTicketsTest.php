<?php

namespace Tests\Feature\Ticket;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('supportRoles')]
    public function test_support_user_with_mine_filter_sees_only_tickets_assigned_to_self(UserRole $role): void
    {
        $supportUser = User::factory()->create([
            'role' => $role,
        ]);

        $otherAgent = User::factory()->create([
            'role' => UserRole::AGENT,
        ]);

        $assignedToSelf = Ticket::factory()->create([
            'title' => 'Assigned to me',
            'assigned_to' => $supportUser->id,
        ]);

        $assignedToSomeoneElse = Ticket::factory()->create([
            'title' => 'Assigned to someone else',
            'assigned_to' => $otherAgent->id,
        ]);

        $unassigned = Ticket::factory()->create([
            'title' => 'Unassigned',
            'assigned_to' => null,
        ]);

        $response = $this->actingAs($supportUser, 'sanctum')
            ->getJson('/api/tickets?mine=true&per_page=100');

        $response->assertOk();

        $ticketIds = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($assignedToSelf->id, $ticketIds);
        $this->assertNotContains($assignedToSomeoneElse->id, $ticketIds);
        $this->assertNotContains($unassigned->id, $ticketIds);
    }

    public function test_customer_with_mine_filter_remains_limited_to_created_tickets(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $otherCustomer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $agent = User::factory()->create([
            'role' => UserRole::AGENT,
        ]);

        $ownTicket = Ticket::factory()->create([
            'title' => 'Own ticket assigned elsewhere',
            'created_by' => $customer->id,
            'assigned_to' => $agent->id,
        ]);

        $otherTicketAssignedToCustomer = Ticket::factory()->create([
            'title' => 'Other customer ticket assigned to requester',
            'created_by' => $otherCustomer->id,
            'assigned_to' => $customer->id,
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/tickets?mine=true&per_page=100');

        $response->assertOk();

        $ticketIds = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($ownTicket->id, $ticketIds);
        $this->assertNotContains($otherTicketAssignedToCustomer->id, $ticketIds);
    }

    #[DataProvider('supportRoles')]
    public function test_support_user_can_filter_tickets_by_assignee(UserRole $role): void
    {
        $supportUser = User::factory()->create([
            'role' => $role,
        ]);

        $targetAgent = User::factory()->create([
            'role' => UserRole::AGENT,
        ]);

        $otherAgent = User::factory()->create([
            'role' => UserRole::AGENT,
        ]);

        $matchingTicket = Ticket::factory()->create([
            'title' => 'Assigned to target',
            'assigned_to' => $targetAgent->id,
        ]);

        $otherAssignedTicket = Ticket::factory()->create([
            'title' => 'Assigned to other agent',
            'assigned_to' => $otherAgent->id,
        ]);

        $unassignedTicket = Ticket::factory()->create([
            'title' => 'Unassigned ticket',
            'assigned_to' => null,
        ]);

        $response = $this->actingAs($supportUser, 'sanctum')
            ->getJson("/api/tickets?assigned_to={$targetAgent->id}&per_page=100");

        $response->assertOk();

        $ticketIds = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($matchingTicket->id, $ticketIds);
        $this->assertNotContains($otherAssignedTicket->id, $ticketIds);
        $this->assertNotContains($unassignedTicket->id, $ticketIds);
    }

    #[DataProvider('priorities')]
    public function test_agent_can_filter_tickets_by_priority(TicketPriority $priority): void
    {
        $agent = User::factory()->create([
            'role' => UserRole::AGENT,
        ]);

        $matchingTicket = Ticket::factory()->create([
            'title' => "Matching {$priority->value} priority",
            'priority' => $priority,
        ]);

        $otherPriorities = collect(TicketPriority::cases())
            ->reject(fn (TicketPriority $candidate) => $candidate === $priority)
            ->values();

        $nonMatchingTickets = $otherPriorities
            ->map(fn (TicketPriority $otherPriority) => Ticket::factory()->create([
                'title' => "Non-matching {$otherPriority->value} priority",
                'priority' => $otherPriority,
            ]));

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson("/api/tickets?priority={$priority->value}&per_page=100");

        $response->assertOk();

        $ticketIds = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($matchingTicket->id, $ticketIds);
        foreach ($nonMatchingTickets as $nonMatchingTicket) {
            $this->assertNotContains($nonMatchingTicket->id, $ticketIds);
        }
    }

    public function test_agent_can_search_tickets_by_title_and_description(): void
    {
        $agent = User::factory()->create([
            'role' => UserRole::AGENT,
        ]);

        $titleMatch = Ticket::factory()->create([
            'title' => 'ROUTER outage escalation',
            'description' => 'General connectivity report.',
        ]);

        $descriptionMatch = Ticket::factory()->create([
            'title' => 'Connectivity report',
            'description' => 'Customer reports a ROUTER reboot loop.',
        ]);

        $nonMatch = Ticket::factory()->create([
            'title' => 'Billing question',
            'description' => 'Invoice copy request.',
        ]);

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson('/api/tickets?search=router&per_page=100');

        $response->assertOk();

        $ticketIds = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($titleMatch->id, $ticketIds);
        $this->assertContains($descriptionMatch->id, $ticketIds);
        $this->assertNotContains($nonMatch->id, $ticketIds);
    }

    public function test_agent_can_sort_tickets_by_requested_field_and_direction(): void
    {
        $agent = User::factory()->create([
            'role' => UserRole::AGENT,
        ]);

        $middleTicket = Ticket::factory()->create([
            'title' => 'Middle ticket',
            'created_at' => Carbon::parse('2026-01-02 12:00:00'),
        ]);

        $oldestTicket = Ticket::factory()->create([
            'title' => 'Oldest ticket',
            'created_at' => Carbon::parse('2026-01-01 12:00:00'),
        ]);

        $newestTicket = Ticket::factory()->create([
            'title' => 'Newest ticket',
            'created_at' => Carbon::parse('2026-01-03 12:00:00'),
        ]);

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson('/api/tickets?sort=created_at&direction=asc&per_page=100');

        $response->assertOk();

        $ticketIds = collect($response->json('data'))->pluck('id')->all();

        $this->assertSame([
            $oldestTicket->id,
            $middleTicket->id,
            $newestTicket->id,
        ], $ticketIds);
    }

    public static function supportRoles(): array
    {
        return [
            'agent' => [UserRole::AGENT],
            'admin' => [UserRole::ADMIN],
        ];
    }

    public static function priorities(): array
    {
        return [
            'high' => [TicketPriority::HIGH],
            'medium' => [TicketPriority::MEDIUM],
            'low' => [TicketPriority::LOW],
        ];
    }
}
