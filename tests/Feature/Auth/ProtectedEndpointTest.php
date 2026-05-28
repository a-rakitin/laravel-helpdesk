<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProtectedEndpointTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('protectedEndpoints')]
    public function test_protected_endpoint_requires_authentication(string $method, string $uri, array $payload = []): void
    {
        $response = match ($method) {
            'GET' => $this->getJson($uri),
            'POST' => $this->postJson($uri, $payload),
            'PATCH' => $this->patchJson($uri, $payload),
        };

        $response->assertUnauthorized();
    }

    #[DataProvider('protectedEndpoints')]
    public function test_protected_endpoint_returns_json_without_json_accept_header(string $method, string $uri, array $payload = []): void
    {
        $response = $this
            ->withHeaders(['Accept' => '*/*'])
            ->call($method, $uri, $payload);

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public static function protectedEndpoints(): array
    {
        return [
            'auth me' => ['GET', '/api/auth/me'],
            'auth logout' => ['POST', '/api/auth/logout'],
            'list users' => ['GET', '/api/users'],
            'change user role' => ['PATCH', '/api/users/1/role', []],
            'list tickets' => ['GET', '/api/tickets'],
            'create ticket' => ['POST', '/api/tickets', []],
            'show ticket' => ['GET', '/api/tickets/1'],
            'assign ticket' => ['PATCH', '/api/tickets/1/assign', []],
            'change ticket status' => ['PATCH', '/api/tickets/1/status', []],
            'list ticket comments' => ['GET', '/api/tickets/1/comments'],
            'create ticket comment' => ['POST', '/api/tickets/1/comments', []],
            'list notifications' => ['GET', '/api/notifications'],
            'mark notification read' => ['POST', '/api/notifications/1/read'],
        ];
    }
}
