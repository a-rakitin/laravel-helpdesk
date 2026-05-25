<?php

namespace Tests\Feature\Ticket;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\TicketCommentAddedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
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

    public function test_comment_notification_builds_mail_message_for_ticket_owner(): void
    {
        $customer = User::factory()->create([
            'name' => 'Customer One',
            'role' => UserRole::CUSTOMER,
        ]);

        $agent = User::factory()->create([
            'role' => UserRole::AGENT,
        ]);

        $ticket = Ticket::create([
            'title' => 'Cannot sign in',
            'description' => 'Login fails after password reset.',
            'created_by' => $customer->id,
            'assigned_to' => $agent->id,
        ]);

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'body' => 'I reset your session.',
        ]);

        $notification = new TicketCommentAddedNotification($ticket, $comment);

        $this->assertSame(['database', 'mail'], $notification->via($customer));

        $mail = $notification->toMail($customer);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame("New comment on ticket #{$ticket->id}", $mail->subject);
        $this->assertContains('A new comment was added to "Cannot sign in".', $mail->introLines);
        $this->assertContains('I reset your session.', $mail->introLines);
    }
}
