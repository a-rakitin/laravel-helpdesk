<?php

namespace Tests\Feature\Notification;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCommentAddedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkNotificationAsReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_own_notification_as_read(): void
    {
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

        $comment = $ticket->comments()->create([
            'user_id' => $agent->id,
            'body' => 'Reply',
        ]);

        $customer->notify(new TicketCommentAddedNotification($ticket, $comment));

        $notification = $customer->notifications()->first();

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertOk()
            ->assertJsonPath('message', 'Notification marked as read.');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $firstUser = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $secondUser = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $agent = User::factory()->create([
            'role' => UserRole::AGENT,
        ]);

        $ticket = Ticket::create([
            'title' => 'Ticket',
            'description' => 'Desc',
            'created_by' => $firstUser->id,
            'assigned_to' => $agent->id,
        ]);

        $comment = $ticket->comments()->create([
            'user_id' => $agent->id,
            'body' => 'Reply',
        ]);

        $firstUser->notify(new TicketCommentAddedNotification($ticket, $comment));

        $notification = $firstUser->notifications()->first();

        $response = $this->actingAs($secondUser, 'sanctum')
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertNotFound();
    }
}
