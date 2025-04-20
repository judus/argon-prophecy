<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing\Middleware;

use Maduser\Argon\Routing\Contracts\MatchedRouteInterface;
use Maduser\Argon\Routing\Contracts\RouteMatcherInterface;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class RouteMatcherMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RouteMatcherInterface $routeMatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->routeMatcher->match($request);

        $this->logger->info('Route matched', [
            'handler' => $route->getHandler(),
            'middlewares' => $route->getMiddleware(),
            'arguments' => $route->getArguments(),
        ]);

        $request = $request->withAttribute(MatchedRouteInterface::class, $route);

        return $handler->handle($request);
    }
}
