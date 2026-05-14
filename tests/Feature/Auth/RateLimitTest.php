<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        User::factory()->create([
            'email' => 'rate-limit-login@example.com',
            'password' => 'correct-password',
        ]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
                ->postJson('/api/auth/login', [
                    'email' => 'rate-limit-login@example.com',
                    'password' => 'incorrect-password',
                ])
                ->assertUnprocessable();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->postJson('/api/auth/login', [
                'email' => 'rate-limit-login@example.com',
                'password' => 'incorrect-password',
            ])
            ->assertTooManyRequests();
    }

    public function test_register_is_rate_limited_after_five_attempts(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.11'])
                ->postJson('/api/auth/register', [
                    'name' => '',
                    'email' => 'not-an-email',
                    'password' => 'short',
                    'password_confirmation' => 'different',
                ])
                ->assertUnprocessable();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.11'])
            ->postJson('/api/auth/register', [
                'name' => '',
                'email' => 'not-an-email',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertTooManyRequests();
    }

    public function test_protected_api_endpoints_are_rate_limited_for_authenticated_users(): void
    {
        $user = User::factory()->create([
            'id' => 9001,
        ]);

        for ($attempt = 1; $attempt <= 60; $attempt++) {
            $this->actingAs($user, 'sanctum')
                ->getJson('/api/auth/me')
                ->assertOk();
        }

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/auth/me')
            ->assertTooManyRequests();
    }
}
