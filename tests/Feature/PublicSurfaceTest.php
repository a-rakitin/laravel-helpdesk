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
            ->assertSee('<link rel="icon" type="image/svg+xml" href="/favicon.svg">', false)
            ->assertSee('<link rel="alternate icon" href="/favicon.ico">', false)
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
            ->assertSee('<link rel="icon" type="image/svg+xml" href="/favicon.svg">', false)
            ->assertSee('<link rel="alternate icon" href="/favicon.ico">', false)
            ->assertSee('https://cdn.jsdelivr.net/npm/@scalar/api-reference', false)
            ->assertSee("url: '/docs/api.json'", false)
            ->assertSee('Scalar.createApiReference', false);
    }

    public function test_favicon_assets_exist_for_browser_tabs(): void
    {
        $svg = file_get_contents(public_path('favicon.svg'));
        $ico = file_get_contents(public_path('favicon.ico'));

        $this->assertIsString($svg);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('#2dd4bf', $svg);

        $this->assertIsString($ico);
        $this->assertGreaterThan(0, strlen($ico));
        $this->assertSame("\x00\x00\x01\x00", substr($ico, 0, 4));
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
