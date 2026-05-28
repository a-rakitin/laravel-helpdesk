<?php

namespace App\Providers;

use App\Models\Ticket;
use App\Models\User;
use App\Policies\TicketPolicy;
use App\Policies\UserPolicy;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by('auth|'.$request->path().'|'.$request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by('api|'.($request->user()?->id ?: $request->ip()));
        });

        Scramble::configure()
            ->expose(
                document: fn (Router $router, $action) => $router->get('docs/api.json', $action)
                    ->name('scramble.docs.document'),
            )
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer')
                );
            });
    }
}
