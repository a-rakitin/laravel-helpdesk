<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_incorrect_password_returns_validation_error(): void
    {
        User::factory()->create([
            'email' => 'agent@example.com',
            'password' => 'correct-password',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'agent@example.com',
            'password' => 'incorrect-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }
}
