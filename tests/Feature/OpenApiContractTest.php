<?php

namespace Tests\Feature;

use Tests\TestCase;

class OpenApiContractTest extends TestCase
{
    public function test_auth_request_bodies_use_named_form_request_schemas(): void
    {
        $document = $this->getJson('/docs/api.json')->assertOk()->json();

        $registerSchema = $document['paths']['/auth/register']['post']['requestBody']['content']['application/json']['schema'] ?? null;
        $loginSchema = $document['paths']['/auth/login']['post']['requestBody']['content']['application/json']['schema'] ?? null;

        $this->assertSame(['$ref' => '#/components/schemas/RegisterRequest'], $registerSchema);
        $this->assertSame(['$ref' => '#/components/schemas/LoginRequest'], $loginSchema);

        $this->assertSame(
            'Customer display name.',
            $document['components']['schemas']['RegisterRequest']['properties']['name']['description'] ?? null,
        );
        $this->assertSame(
            'Must match the password field.',
            $document['components']['schemas']['RegisterRequest']['properties']['password_confirmation']['description'] ?? null,
        );
        $this->assertSame(
            'Registered user email address.',
            $document['components']['schemas']['LoginRequest']['properties']['email']['description'] ?? null,
        );
    }

    public function test_register_confirmation_schema_preserves_password_minimum_length(): void
    {
        $document = $this->getJson('/docs/api.json')->assertOk()->json();

        $this->assertSame(
            8,
            $document['components']['schemas']['RegisterRequest']['properties']['password_confirmation']['minLength'] ?? null,
        );
    }

    public function test_auth_response_examples_remain_consistent(): void
    {
        $document = $this->getJson('/docs/api.json')->assertOk()->json();

        $user = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'customer',
            'created_at' => '2026-08-30T11:00:00.000000Z',
            'updated_at' => '2026-08-30T11:00:00.000000Z',
        ];

        $authenticatedUser = [
            'user' => $user,
            'token' => '1|aB3dEfGhIjKlMnOpQrStUvWxYz0123456789AbCd',
        ];

        $this->assertSame(
            [$authenticatedUser],
            $document['paths']['/auth/register']['post']['responses']['201']['content']['application/json']['schema']['examples'] ?? null,
        );
        $this->assertSame(
            [$authenticatedUser],
            $document['paths']['/auth/login']['post']['responses']['200']['content']['application/json']['schema']['examples'] ?? null,
        );
        $this->assertSame(
            [['user' => $user]],
            $document['paths']['/auth/me']['get']['responses']['200']['content']['application/json']['schema']['examples'] ?? null,
        );
    }

    public function test_ticket_response_examples_remain_consistent(): void
    {
        $document = $this->getJson('/docs/api.json')->assertOk()->json();

        $listedTicket = [
            'id' => 42,
            'title' => 'Cannot access account',
            'description' => 'The user cannot sign in after resetting the password.',
            'status' => 'open',
            'priority' => 'high',
            'created_by' => 1,
            'assigned_to' => 3,
            'created_at' => '2026-08-27T09:15:30.000000Z',
            'updated_at' => '2026-08-27T09:15:30.000000Z',
        ];

        $createdTicket = [
            'id' => 42,
            'title' => 'Cannot sign in',
            'description' => 'Login fails after password reset.',
            'status' => 'open',
            'priority' => 'high',
            'created_by' => 1,
            'assigned_to' => null,
            'created_at' => '2026-08-28T09:15:30.000000Z',
            'updated_at' => '2026-08-28T09:15:30.000000Z',
        ];

        $requestedTicket = [
            'id' => 42,
            'title' => 'Cannot access account',
            'description' => 'The user cannot sign in after resetting the password.',
            'status' => 'open',
            'priority' => 'high',
            'created_by' => 1,
            'assigned_to' => 3,
            'created_at' => '2026-08-29T09:15:30.000000Z',
            'updated_at' => '2026-08-29T09:15:30.000000Z',
        ];

        $assignedTicket = [
            'id' => 42,
            'title' => 'Cannot access account',
            'description' => 'The user cannot sign in after resetting the password.',
            'status' => 'open',
            'priority' => 'high',
            'created_by' => 1,
            'assigned_to' => 2,
            'created_at' => '2026-08-28T09:15:30.000000Z',
            'updated_at' => '2026-08-29T10:20:00.000000Z',
        ];

        $statusChangedTicket = [
            'id' => 42,
            'title' => 'Cannot access account',
            'description' => 'The user cannot sign in after resetting the password.',
            'status' => 'in_progress',
            'priority' => 'high',
            'created_by' => 1,
            'assigned_to' => 2,
            'created_at' => '2026-08-28T09:15:30.000000Z',
            'updated_at' => '2026-08-29T10:30:00.000000Z',
        ];

        $pagination = [
            'current_page' => 1,
            'per_page' => 15,
            'total' => 1,
            'last_page' => 1,
        ];

        $this->assertSame(
            [['data' => [$listedTicket], 'meta' => $pagination]],
            $document['paths']['/tickets']['get']['responses']['200']['content']['application/json']['schema']['examples'] ?? null,
        );
        $this->assertSame(
            [['data' => $createdTicket]],
            $document['paths']['/tickets']['post']['responses']['201']['content']['application/json']['schema']['examples'] ?? null,
        );
        $this->assertSame(
            [['data' => $requestedTicket]],
            $document['paths']['/tickets/{ticket}']['get']['responses']['200']['content']['application/json']['schema']['examples'] ?? null,
        );
        $this->assertSame(
            [['data' => $assignedTicket]],
            $document['paths']['/tickets/{ticket}/assign']['patch']['responses']['200']['content']['application/json']['schema']['examples'] ?? null,
        );
        $this->assertSame(
            [['data' => $statusChangedTicket]],
            $document['paths']['/tickets/{ticket}/status']['patch']['responses']['200']['content']['application/json']['schema']['examples'] ?? null,
        );
    }

    public function test_docs_api_json_exposes_stable_api_contract(): void
    {
        $response = $this->getJson('/docs/api.json');

        $response->assertOk();

        $document = $response->json();
        $paths = $document['paths'] ?? [];

        $this->assertSame('3.1.0', $document['openapi'] ?? null);
        $this->assertSame('Laravel Helpdesk API Docs', $document['info']['title'] ?? null);
        $this->assertSame('1.0.0', $document['info']['version'] ?? null);

        $serverPaths = array_map(
            fn (string $url): ?string => parse_url($url, PHP_URL_PATH),
            array_column($document['servers'] ?? [], 'url'),
        );

        $this->assertContains('/api', $serverPaths);

        $this->assertSame(
            [
                'type' => 'http',
                'scheme' => 'bearer',
            ],
            $document['components']['securitySchemes']['http'] ?? null,
        );

        $expectedPaths = [
            '/auth/register',
            '/auth/login',
            '/auth/me',
            '/auth/logout',
            '/users',
            '/users/{user}/role',
            '/tickets',
            '/tickets/{ticket}',
            '/tickets/{ticket}/assign',
            '/tickets/{ticket}/status',
            '/tickets/{ticket}/comments',
            '/notifications',
            '/notifications/{id}/read',
        ];

        $this->assertSame([], array_values(array_diff($expectedPaths, array_keys($paths))));

        foreach (['/', '/docs/api', '/docs/api.json'] as $publicPath) {
            $this->assertArrayNotHasKey($publicPath, $paths);
        }

        $this->assertOperationIsUnauthenticated($paths, '/auth/register', 'post');
        $this->assertOperationIsUnauthenticated($paths, '/auth/login', 'post');

        foreach ($this->protectedOperations() as [$path, $method]) {
            $this->assertProtectedOperationDoesNotDisableSecurity($paths, $path, $method);
        }

        foreach ($this->rateLimitedOperations() as [$path, $method]) {
            $this->assertOperationDocumentsRateLimitResponse($paths, $path, $method);
        }

        $this->assertOperationDocumentsResponses($paths, '/users', 'get', [200, 401, 403, 422]);
        $this->assertOperationDocumentsResponses($paths, '/users/{user}/role', 'patch', [200, 401, 403, 404, 422]);
    }

    /**
     * @param  array<string, mixed>  $paths
     */
    private function assertOperationIsUnauthenticated(array $paths, string $path, string $method): void
    {
        $operation = $paths[$path][$method] ?? null;

        $this->assertIsArray($operation, "{$method} {$path} must exist in the OpenAPI document.");
        $this->assertSame([], $operation['security'] ?? null, "{$method} {$path} must explicitly disable bearer auth.");
    }

    /**
     * @param  array<string, mixed>  $paths
     */
    private function assertProtectedOperationDoesNotDisableSecurity(array $paths, string $path, string $method): void
    {
        $operation = $paths[$path][$method] ?? null;

        $this->assertIsArray($operation, "{$method} {$path} must exist in the OpenAPI document.");

        if (array_key_exists('security', $operation)) {
            $this->assertNotSame([], $operation['security'], "{$method} {$path} must not opt out of bearer auth.");
        }
    }

    /**
     * @param  array<string, mixed>  $paths
     * @param  array<int, int>  $statuses
     */
    private function assertOperationDocumentsResponses(array $paths, string $path, string $method, array $statuses): void
    {
        $operation = $paths[$path][$method] ?? null;

        $this->assertIsArray($operation, "{$method} {$path} must exist in the OpenAPI document.");

        foreach ($statuses as $status) {
            $this->assertArrayHasKey((string) $status, $operation['responses'] ?? [], "{$method} {$path} must document {$status}.");
        }
    }

    /**
     * @param  array<string, mixed>  $paths
     */
    private function assertOperationDocumentsRateLimitResponse(array $paths, string $path, string $method): void
    {
        $response = $paths[$path][$method]['responses']['429'] ?? null;

        $this->assertIsArray($response, "{$method} {$path} must document 429.");
        $this->assertSame('Too many requests', $response['description'] ?? null);
        $this->assertSame('object', $response['content']['application/json']['schema']['type'] ?? null);
        $this->assertSame('string', $response['content']['application/json']['schema']['properties']['message']['type'] ?? null);
        $this->assertContains('message', $response['content']['application/json']['schema']['required'] ?? []);
    }

    /**
     * @return array<int, array{string, string}>
     */
    private function protectedOperations(): array
    {
        return [
            ['/auth/me', 'get'],
            ['/auth/logout', 'post'],
            ['/users', 'get'],
            ['/users/{user}/role', 'patch'],
            ['/tickets', 'get'],
            ['/tickets', 'post'],
            ['/tickets/{ticket}', 'get'],
            ['/tickets/{ticket}/assign', 'patch'],
            ['/tickets/{ticket}/status', 'patch'],
            ['/tickets/{ticket}/comments', 'get'],
            ['/tickets/{ticket}/comments', 'post'],
            ['/notifications', 'get'],
            ['/notifications/{id}/read', 'post'],
        ];
    }

    /**
     * @return array<int, array{string, string}>
     */
    private function rateLimitedOperations(): array
    {
        return [
            ['/auth/register', 'post'],
            ['/auth/login', 'post'],
            ...$this->protectedOperations(),
        ];
    }
}
