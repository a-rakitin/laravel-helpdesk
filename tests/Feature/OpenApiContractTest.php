<?php

namespace Tests\Feature;

use Tests\TestCase;

class OpenApiContractTest extends TestCase
{
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
     * @return array<int, array{string, string}>
     */
    private function protectedOperations(): array
    {
        return [
            ['/auth/me', 'get'],
            ['/auth/logout', 'post'],
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
}
