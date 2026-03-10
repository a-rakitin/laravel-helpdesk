<?php

namespace Tests\Feature\Notification;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarkNotificationAsReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $notificationId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\\Notifications\\TicketCommentAddedNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $customer->id,
            'data' => json_encode([
                'ticket_id' => 1,
                'ticket_title' => 'Test ticket',
                'comment_id' => 1,
                'comment_body' => 'Reply',
                'comment_author_id' => 1,
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/notifications/{$notificationId}/read");

        $response->assertOk()
            ->assertJsonPath('message', 'Notification marked as read.');

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationId,
            'notifiable_id' => $customer->id,
        ]);

        $this->assertNotNull(
            DB::table('notifications')->where('id', $notificationId)->value('read_at')
        );
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $firstUser = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $secondUser = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $notificationId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\\Notifications\\TicketCommentAddedNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $firstUser->id,
            'data' => json_encode([
                'ticket_id' => 1,
                'ticket_title' => 'Test ticket',
                'comment_id' => 1,
                'comment_body' => 'Reply',
                'comment_author_id' => 1,
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($secondUser, 'sanctum')
            ->postJson("/api/notifications/{$notificationId}/read");

        $response->assertNotFound();
    }
}
