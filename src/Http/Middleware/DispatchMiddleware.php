<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Middleware;

use Closure;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Routing\Contracts\MatchedRouteInterface;
use Maduser\Argon\Routing\RouteMiddlewarePipeline;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final readonly class DispatchMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ArgonContainer $container,
        private RouteMiddlewarePipeline $pipeline
    ) {
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var MatchedRouteInterface|null $route */
        $route = $request->getAttribute(MatchedRouteInterface::class);

        if (!$route instanceof MatchedRouteInterface) {
            throw new RuntimeException('No resolved route found in request.');
        }

        $routeHandler = $route->getHandler();

        if ($routeHandler instanceof Closure) {
            // TODO: Future support for closure handlers
            throw new RuntimeException('Closure route handlers are not yet supported.');
        }

        $serviceId = (string) $routeHandler;

        $invoker = $this->container->get($serviceId);

        if (!is_callable($invoker)) {
            $type = get_debug_type($invoker);
            throw new RuntimeException("Handler [$serviceId] is not callable (got: $type).");
        }

        $callable = fn(): mixed => $invoker($route->getArguments());

        return $this->pipeline->handle(
            $route->getMiddleware(),
            $callable,
            $request,
            $handler
        );
    }
}
