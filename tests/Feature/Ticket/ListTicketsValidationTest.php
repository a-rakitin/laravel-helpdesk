<?php

namespace Tests\Feature\Ticket;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ListTicketsValidationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('invalidListFilters')]
    public function test_invalid_list_ticket_filter_returns_validation_error(string $query, string $field): void
    {
        $agent = User::factory()->create([
            'role' => UserRole::AGENT,
        ]);

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson("/api/tickets?{$query}");

        $response->assertUnprocessable()
            ->assertJsonValidationErrors($field);
    }

    public static function invalidListFilters(): array
    {
        return [
            'invalid status' => ['status=archived', 'status'],
            'invalid priority' => ['priority=urgent', 'priority'],
            'invalid assigned_to type' => ['assigned_to=agent', 'assigned_to'],
            'missing assigned_to user' => ['assigned_to=999999', 'assigned_to'],
            'invalid mine boolean' => ['mine=definitely', 'mine'],
            'search too long' => ['search='.str_repeat('a', 256), 'search'],
            'invalid sort' => ['sort=title', 'sort'],
            'invalid direction' => ['direction=random', 'direction'],
            'per_page below minimum' => ['per_page=0', 'per_page'],
            'per_page above maximum' => ['per_page=101', 'per_page'],
            'per_page not integer' => ['per_page=many', 'per_page'],
        ];
    }
}
