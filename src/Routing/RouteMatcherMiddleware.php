<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use Maduser\Argon\Routing\Contracts\MatchedRouteInterface;
use Maduser\Argon\Routing\Contracts\RouteMatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class RouteMatcherMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RouteMatcherInterface $routeMatcher,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->routeMatcher->match($request);

        $request = $request->withAttribute(MatchedRouteInterface::class, $route);

        return $handler->handle($request);
    }
}
