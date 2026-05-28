<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_access_me_endpoint(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                'token',
            ]);

        $token = $response->json('token');

        $me = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/auth/me');

        $me->assertOk()
            ->assertJsonPath('user.email', 'john@example.com');
    }

    public function test_register_ignores_submitted_role_and_creates_customer(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Role Escalation Attempt',
            'email' => 'role-escalation@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::ADMIN->value,
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.role', UserRole::CUSTOMER->value);

        $user = User::where('email', 'role-escalation@example.com')->firstOrFail();

        $this->assertTrue($user->isCustomer());
    }
}
