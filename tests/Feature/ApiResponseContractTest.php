<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiResponseContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_success_responses_keep_user_and_token_shape(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Contract Customer',
            'email' => 'contract-customer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $register->assertCreated()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role', 'created_at', 'updated_at'],
                'token',
            ])
            ->assertJsonMissingPath('data')
            ->assertJsonPath('user.role', UserRole::CUSTOMER->value);

        $token = $register->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role', 'created_at', 'updated_at'],
            ])
            ->assertJsonMissingPath('data');

        $loginUser = User::factory()->create([
            'email' => 'contract-agent@example.com',
            'password' => 'password',
            'role' => UserRole::AGENT,
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => $loginUser->email,
            'password' => 'password',
        ]);

        $login->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role', 'created_at', 'updated_at'],
                'token',
            ])
            ->assertJsonMissingPath('data')
            ->assertJsonPath('user.role', UserRole::AGENT->value);
    }

    public function test_ticket_success_responses_are_wrapped_in_data(): void
    {
        $agent = User::factory()->create(['role' => UserRole::AGENT]);
        $assignee = User::factory()->create(['role' => UserRole::AGENT]);

        $created = $this->actingAs($agent, 'sanctum')
            ->postJson('/api/tickets', [
                'title' => 'Contract ticket',
                'description' => 'Contract response shape',
                'priority' => 'high',
            ]);

        $created->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'created_by',
                    'assigned_to',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.assigned_to', null);

        $ticketId = $created->json('data.id');

        $this->actingAs($agent, 'sanctum')
            ->getJson("/api/tickets/{$ticketId}")
            ->assertOk()
            ->assertJsonPath('data.id', $ticketId);

        $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/tickets/{$ticketId}/assign", [
                'assigned_to' => $assignee->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.assigned_to', $assignee->id);

        $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/tickets/{$ticketId}/status", [
                'status' => TicketStatus::IN_PROGRESS->value,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', TicketStatus::IN_PROGRESS->value);
    }

    public function test_ticket_list_success_response_exposes_data_and_pagination_meta(): void
    {
        $agent = User::factory()->create(['role' => UserRole::AGENT]);

        Ticket::factory()->count(2)->create();

        $this->actingAs($agent, 'sanctum')
            ->getJson('/api/tickets?per_page=1')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'priority',
                        'created_by',
                        'assigned_to',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ])
            ->assertJsonMissingPath('links');
    }

    public function test_ticket_comment_success_responses_are_wrapped_in_data(): void
    {
        $agent = User::factory()->create(['role' => UserRole::AGENT]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $ticket = Ticket::factory()->create(['created_by' => $customer->id]);

        $created = $this->actingAs($agent, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/comments", [
                'body' => 'Contract comment',
            ]);

        $created->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'ticket_id',
                    'user_id',
                    'body',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->actingAs($agent, 'sanctum')
            ->getJson("/api/tickets/{$ticket->id}/comments")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'ticket_id',
                        'user_id',
                        'body',
                        'created_at',
                        'updated_at',
                        'author' => ['id', 'name', 'email', 'role', 'created_at', 'updated_at'],
                    ],
                ],
            ])
            ->assertJsonMissingPath('meta');
    }

    public function test_notification_list_success_response_is_wrapped_in_data(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $notificationId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\\Notifications\\TicketCommentAddedNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $customer->id,
            'data' => json_encode([
                'ticket_id' => 1,
                'ticket_title' => 'Contract ticket',
                'comment_id' => 1,
                'comment_body' => 'Reply',
                'comment_author_id' => 1,
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'data', 'read_at', 'created_at'],
                ],
            ])
            ->assertJsonMissingPath('meta')
            ->assertJsonPath('data.0.data.ticket_title', 'Contract ticket')
            ->assertJsonPath('data.0.id', $notificationId);
    }
}
