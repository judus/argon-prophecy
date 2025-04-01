<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use Maduser\Argon\Routing\Contracts\ResolvedRouteInterface;
use Maduser\Argon\Routing\Contracts\RouteContextProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class DefaultRouteContextProvider implements RouteContextProviderInterface
{
    public function __construct(
        private ServerRequestInterface $request
    ) {
    }

    public function getRoute(): ResolvedRouteInterface
    {
        $route = $this->request->getAttribute(ResolvedRouteInterface::class);

        if (!$route instanceof ResolvedRouteInterface) {
            throw new \RuntimeException('Route not resolved in request.');
        }

        return $route;
    }
}
