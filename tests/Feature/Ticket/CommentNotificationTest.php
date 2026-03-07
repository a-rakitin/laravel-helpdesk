<?php

namespace Tests\Feature\Ticket;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCommentAddedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CommentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_creation_dispatches_notification_to_ticket_owner(): void
    {
        Notification::fake();

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
            'assigned_to' => $agent->id,
        ]);

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/comments", [
                'body' => 'Agent reply',
            ]);

        $response->assertCreated();

        Notification::assertSentTo(
            [$customer],
            TicketCommentAddedNotification::class
        );

        Notification::assertNotSentTo(
            [$agent],
            TicketCommentAddedNotification::class
        );
    }
}
