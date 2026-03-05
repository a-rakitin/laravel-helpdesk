<?php

namespace Tests\Feature\Ticket;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_ticket(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tickets', [
                'title' => 'Test ticket',
                'description' => 'Test description',
                'priority' => 'high',
            ]);

        $response->assertCreated()
            ->assertJsonPath('title', 'Test ticket');

        $this->assertDatabaseHas('tickets', [
            'title' => 'Test ticket',
            'created_by' => $user->id,
        ]);
    }
}
