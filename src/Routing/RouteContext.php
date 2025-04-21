<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use Maduser\Argon\Routing\Contracts\MatchedRouteInterface;
use Maduser\Argon\Routing\Contracts\RouteContextInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class RouteContext implements RouteContextInterface
{
    public function set(ServerRequestInterface $request, MatchedRouteInterface $route): ServerRequestInterface
    {
        return $request->withAttribute(MatchedRouteInterface::class, $route);
    }

    public function get(ServerRequestInterface $request): MatchedRouteInterface
    {
        $route = $request->getAttribute(MatchedRouteInterface::class);

        if (!$route instanceof MatchedRouteInterface) {
            throw new RuntimeException('Matched route not found.');
        }

        return $route;
    }

    public function tryGet(ServerRequestInterface $request): ?MatchedRouteInterface
    {
        /** @var MatchedRouteInterface|null $route */
        $route = $request->getAttribute(MatchedRouteInterface::class);

        return $route instanceof MatchedRouteInterface ? $route : null;
    }
}
