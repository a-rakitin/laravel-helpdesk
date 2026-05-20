<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicSurfaceTest extends TestCase
{
    public function test_root_shows_public_landing_page_with_project_links(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertSee('<title>Helpdesk API</title>', false)
            ->assertSee('rel="icon"', false)
            ->assertSee('data:image/svg+xml', false)
            ->assertSee('Helpdesk API', false)
            ->assertSee('Helpdesk ticket workflow API', false)
            ->assertSee('data-theme="dark"', false)
            ->assertSee('data-lang-toggle', false)
            ->assertSee('GitHub', false)
            ->assertSee('Local setup', false)
            ->assertSee('href="/docs/api"', false)
            ->assertSee('href="/docs/api.json"', false)
            ->assertSee('href="https://github.com/a-rakitin/laravel-helpdesk"', false)
            ->assertSee('href="https://github.com/a-rakitin/laravel-helpdesk#local-setup"', false)
            ->assertSee('data-local-setup-link', false)
            ->assertSee('data-local-setup-href-en="https://github.com/a-rakitin/laravel-helpdesk#local-setup"', false)
            ->assertSee('data-local-setup-href-ru="https://github.com/a-rakitin/laravel-helpdesk/blob/main/README.ru.md#локальный-запуск"', false)
            ->assertSee('href="https://github.com/a-rakitin/laravel-helpdesk/tree/main/postman"', false)
            ->assertDontSee('"status":"ok"', false);

        $content = $response->getContent();

        $this->assertSame(1, substr_count($content, 'href="/docs/api"'));
        $this->assertSame(1, substr_count($content, 'href="/docs/api.json"'));
    }

    public function test_docs_api_shows_interactive_documentation(): void
    {
        $response = $this->get('/docs/api');

        $response->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertSee('<title>Helpdesk API Docs</title>', false)
            ->assertSee('https://cdn.jsdelivr.net/npm/@scalar/api-reference', false)
            ->assertSee("url: '/docs/api.json'", false)
            ->assertSee('Scalar.createApiReference', false);
    }

    public function test_docs_api_json_keeps_openapi_document_route(): void
    {
        $response = $this->getJson('/docs/api.json');

        $response->assertOk()
            ->assertJsonStructure([
                'openapi',
                'info' => ['title', 'version'],
                'paths',
            ]);
    }
}
