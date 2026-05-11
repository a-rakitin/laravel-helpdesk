<?php

namespace Tests\Feature\Ticket;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_comment_on_own_ticket(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $ticket = Ticket::create([
            'title' => 'My ticket',
            'description' => 'Desc',
            'created_by' => $customer->id,
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/comments", [
                'body' => 'My first comment',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.body', 'My first comment');

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $customer->id,
            'body' => 'My first comment',
        ]);
    }

    public function test_customer_cannot_comment_on_other_users_ticket(): void
    {
        $owner = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $otherCustomer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $ticket = Ticket::create([
            'title' => 'Other ticket',
            'description' => 'Desc',
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($otherCustomer, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/comments", [
                'body' => 'Should fail',
            ]);

        $response->assertForbidden();
    }

    public function test_agent_can_comment_on_customer_ticket(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $agent = User::factory()->create([
            'role' => UserRole::AGENT,
        ]);

        $ticket = Ticket::create([
            'title' => 'Customer ticket',
            'description' => 'Desc',
            'created_by' => $customer->id,
        ]);

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/comments", [
                'body' => 'Agent response',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.body', 'Agent response');

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'body' => 'Agent response',
        ]);
    }

    public function test_admin_can_comment_on_customer_ticket(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $ticket = Ticket::create([
            'title' => 'Customer ticket',
            'description' => 'Desc',
            'created_by' => $customer->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/comments", [
                'body' => 'Admin response',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.body', 'Admin response');

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'body' => 'Admin response',
        ]);
    }
}
