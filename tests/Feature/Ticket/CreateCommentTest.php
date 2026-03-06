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
            ->assertJsonPath('body', 'My first comment');

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
}
