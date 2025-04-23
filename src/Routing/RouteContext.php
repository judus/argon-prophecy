<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use JsonSerializable;
use Maduser\Argon\Routing\Contracts\MatchedRouteInterface;
use Maduser\Argon\Routing\Contracts\RouteContextInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class RouteContext implements RouteContextInterface, JsonSerializable
{
    private ?MatchedRouteInterface $matchedRoute = null;

    public function set(ServerRequestInterface $request, MatchedRouteInterface $route): ServerRequestInterface
    {
        $this->matchedRoute = $route;
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

    public function jsonSerialize(): array
    {
        return $this->matchedRoute?->toArray() ?? [];
    }
}
