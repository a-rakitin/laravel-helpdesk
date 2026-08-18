<?php

namespace App\OpenApi;

use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\RouteInfo;
use Dedoc\Scramble\Support\Type\ObjectType;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class RateLimitResponseExtension extends OperationExtension
{
    public function handle(Operation $operation, RouteInfo $routeInfo): void
    {
        $isRateLimited = collect($routeInfo->route->gatherMiddleware())
            ->contains(fn (mixed $middleware): bool => is_string($middleware)
                && ($this->isThrottleAlias($middleware) || $this->isThrottleMiddleware($middleware)));

        if (! $isRateLimited) {
            return;
        }

        $response = $this->openApiTransformer->toResponse(
            new ObjectType(TooManyRequestsHttpException::class),
        );

        if ($response) {
            $operation->addResponse($response);
        }
    }

    private function isThrottleAlias(string $middleware): bool
    {
        return str_starts_with($middleware, 'throttle:');
    }

    private function isThrottleMiddleware(string $middleware): bool
    {
        return str_starts_with($middleware, ThrottleRequests::class.':');
    }
}
