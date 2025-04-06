<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use Maduser\Argon\Routing\Contracts\MatchedRouteInterface;
use Maduser\Argon\Routing\Contracts\RouteContextProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class DefaultRouteContextProvider implements RouteContextProviderInterface
{
    public function __construct(
        private ServerRequestInterface $request
    ) {
    }

    public function getRoute(): MatchedRouteInterface
    {
        $route = $this->request->getAttribute(MatchedRouteInterface::class);

        if (!$route instanceof MatchedRouteInterface) {
            throw new \RuntimeException('Route not resolved in request.');
        }

        return $route;
    }
}
