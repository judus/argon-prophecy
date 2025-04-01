<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing\Adapters;

use App\Middlewares\DumbMiddleware;
use Maduser\Argon\Http\Controllers\ArgonController;
use Maduser\Argon\Routing\Contracts\ResolvedRouteInterface;
use Maduser\Argon\Routing\Contracts\RouterInterface;
use Maduser\Argon\Routing\MatchedRoute;
use Psr\Http\Message\ServerRequestInterface;

final class ArgonRouteAdapter implements RouterInterface
{
    public function match(ServerRequestInterface $request): ResolvedRouteInterface
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        // 🔥 HARD-CODED for now — this is just a placeholder
        if ($path === '/' && $method === 'GET') {
            return new MatchedRoute(
                handler: ArgonController::class,
                middleware: [DumbMiddleware::class],
                parameters: []
            );
        }

        throw new \RuntimeException(sprintf(
            'No route matched %s %s',
            $method,
            $path
        ));
    }
}