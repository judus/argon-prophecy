<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Middleware;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Routing\RouteHandler;
use Maduser\Argon\Routing\Contracts\ResolvedRouteInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final readonly class DispatchMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ArgonContainer $container,
        private PerRouteMiddlewareRunner $runner
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ResolvedRouteInterface|null $route */
        $route = $request->getAttribute(ResolvedRouteInterface::class);

        if (!$route instanceof ResolvedRouteInterface) {
            throw new RuntimeException('No resolved route found in request.');
        }

        $handlerDef = $route->getHandler();
        $routeArgs = $route->getParameters() ?? [];

        $controller = fn() => $this->invokeHandler($handlerDef, $request, $routeArgs);

        return $this->runner->run(
            $route->getMiddleware(),
            $controller,
            $request,
            $handler
        );
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    private function invokeHandler(RouteHandler|callable|array|string $handler, ServerRequestInterface $request, array $routeArgs): mixed
    {
        if ($handler instanceof RouteHandler) {
            return $handler($request);
        }

        if (is_array($handler)) {
            [$classOrCallable, $method] = [$handler[0], $handler[1] ?? null];
            return $this->container->invoke($classOrCallable, $method, $routeArgs);
        }

        if (is_string($handler) && class_exists($handler)) {
            return $this->container->invoke($handler, '__invoke', $routeArgs);
        }

        if (is_callable($handler)) {
            return $this->container->invoke($handler, null, $routeArgs);
        }

        throw new RuntimeException(sprintf(
            'Invalid route handler provided: [%s]',
            get_debug_type($handler)
        ));
    }
}
