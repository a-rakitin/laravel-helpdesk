<?php

namespace Tests\Feature\Ticket;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketMutationValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_create_validation_rejects_invalid_payload(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tickets', [
                'priority' => 'urgent',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'description', 'priority']);
    }

    public function test_assign_validation_rejects_missing_assignee(): void
    {
        $agent = User::factory()->create(['role' => UserRole::AGENT]);
        $ticket = Ticket::factory()->create(['created_by' => $agent->id]);

        $response = $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/tickets/{$ticket->id}/assign", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('assigned_to');
    }

    public function test_status_change_validation_rejects_invalid_status(): void
    {
        $agent = User::factory()->create(['role' => UserRole::AGENT]);
        $ticket = Ticket::factory()->create(['created_by' => $agent->id]);

        $response = $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/tickets/{$ticket->id}/status", [
                'status' => 'archived',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_comment_create_validation_rejects_missing_body(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $ticket = Ticket::factory()->create(['created_by' => $customer->id]);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/comments", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('body');
    }

    public function test_assign_authorization_still_runs_before_validation(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $ticket = Ticket::factory()->create(['created_by' => $customer->id]);

        $response = $this->actingAs($customer, 'sanctum')
            ->patchJson("/api/tickets/{$ticket->id}/assign", []);

        $response->assertForbidden();
    }

    public function test_status_change_authorization_still_runs_before_validation(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $ticket = Ticket::factory()->create(['created_by' => $customer->id]);

        $response = $this->actingAs($customer, 'sanctum')
            ->patchJson("/api/tickets/{$ticket->id}/status", []);

        $response->assertForbidden();
    }

    public function test_comment_create_authorization_still_runs_before_validation(): void
    {
        $owner = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $otherCustomer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $ticket = Ticket::factory()->create(['created_by' => $owner->id]);

        $response = $this->actingAs($otherCustomer, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/comments", []);

        $response->assertForbidden();
    }
}
