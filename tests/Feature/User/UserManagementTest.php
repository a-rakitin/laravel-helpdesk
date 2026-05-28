<?php

namespace Tests\Feature\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $agent = User::factory()->create(['role' => UserRole::AGENT]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/users?per_page=100');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'role', 'created_at', 'updated_at'],
                ],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ])
            ->assertJsonMissingPath('links')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.per_page', 100);

        $userIds = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($admin->id, $userIds);
        $this->assertContains($agent->id, $userIds);
        $this->assertContains($customer->id, $userIds);
    }

    public function test_agent_cannot_list_users(): void
    {
        $agent = User::factory()->create(['role' => UserRole::AGENT]);

        $this->actingAs($agent, 'sanctum')
            ->getJson('/api/users')
            ->assertForbidden();
    }

    public function test_customer_cannot_list_users(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/users')
            ->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_list_users(): void
    {
        $this->getJson('/api/users')
            ->assertUnauthorized();
    }

    public function test_admin_can_change_customer_to_agent(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/users/{$customer->id}/role", [
                'role' => UserRole::AGENT->value,
            ])
            ->assertOk()
            ->assertJsonPath('data.id', $customer->id)
            ->assertJsonPath('data.role', UserRole::AGENT->value);

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'role' => UserRole::AGENT->value,
        ]);
    }

    public function test_admin_can_change_agent_to_admin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $agent = User::factory()->create(['role' => UserRole::AGENT]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/users/{$agent->id}/role", [
                'role' => UserRole::ADMIN->value,
            ])
            ->assertOk()
            ->assertJsonPath('data.id', $agent->id)
            ->assertJsonPath('data.role', UserRole::ADMIN->value);

        $this->assertDatabaseHas('users', [
            'id' => $agent->id,
            'role' => UserRole::ADMIN->value,
        ]);
    }

    public function test_invalid_role_returns_validation_error(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/users/{$customer->id}/role", [
                'role' => 'owner',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('role');
    }

    public function test_missing_user_returns_not_found_when_changing_role(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/users/999999/role', [
                'role' => UserRole::AGENT->value,
            ])
            ->assertNotFound();
    }

    #[DataProvider('nonAdminRoles')]
    public function test_non_admin_users_cannot_change_roles(UserRole $role): void
    {
        $actor = User::factory()->create(['role' => $role]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($actor, 'sanctum')
            ->patchJson("/api/users/{$customer->id}/role", [
                'role' => UserRole::AGENT->value,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'role' => UserRole::CUSTOMER->value,
        ]);
    }

    public function test_cannot_demote_the_last_admin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/users/{$admin->id}/role", [
                'role' => UserRole::AGENT->value,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('role');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => UserRole::ADMIN->value,
        ]);
    }

    public function test_admin_can_demote_self_when_another_admin_remains(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/users/{$admin->id}/role", [
                'role' => UserRole::CUSTOMER->value,
            ])
            ->assertOk()
            ->assertJsonPath('data.id', $admin->id)
            ->assertJsonPath('data.role', UserRole::CUSTOMER->value);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => UserRole::CUSTOMER->value,
        ]);
    }

    public static function nonAdminRoles(): array
    {
        return [
            'agent' => [UserRole::AGENT],
            'customer' => [UserRole::CUSTOMER],
        ];
    }
}
