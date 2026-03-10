<?php

namespace Tests\Feature\Notification;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCommentAddedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ListNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_notifications(): void
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

        $customer->notify(new TicketCommentAddedNotification(
            $ticket,
            $ticket->comments()->create([
                'user_id' => $agent->id,
                'body' => 'Reply',
            ])
        ));

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/notifications');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'data', 'read_at', 'created_at'],
                ],
            ]);
    }
}
