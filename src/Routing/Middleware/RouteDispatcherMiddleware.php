<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing\Middleware;

use Closure;
use Maduser\Argon\Routing\Contracts\MatchedRouteInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final readonly class RouteDispatcherMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var MatchedRouteInterface|null $route */
        $route = $request->getAttribute(MatchedRouteInterface::class);

        if (!$route instanceof MatchedRouteInterface) {
            throw new RuntimeException('No matched route found in request attributes.');
        }

        $handlerDef = $route->getHandler();

        if ($handlerDef instanceof Closure) {
            $invoker = $handlerDef;
        } else {
            $serviceId = (string) $handlerDef;

            if ($serviceId === self::class) {
                throw new RuntimeException('Infinite RouteDispatcherMiddleware loop detected.');
            }

            $invoker = $this->container->get($serviceId);

            if (!is_callable($invoker)) {
                $type = get_debug_type($invoker);
                throw new RuntimeException("Handler [$serviceId] is not callable (got: $type).");
            }
        }

        $args = $route->getArguments();

        $rawResult = $invoker($args);

        $request = $request->withAttribute('rawResult', $rawResult);

        return $handler->handle($request);
    }
}
