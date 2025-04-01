<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Middleware;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Routing\Contracts\ResolvedRouteInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class DispatchMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ArgonContainer $container,
        private PerRouteMiddlewareRunner $runner
    ) {
    }

    /**
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ResolvedRouteInterface|null $route */
        $route = $request->getAttribute(ResolvedRouteInterface::class);

        if (!$route instanceof ResolvedRouteInterface) {
            throw new \RuntimeException('No resolved route found in request.');
        }

        $handlerDef = $route->getHandler();

        $controller = match (true) {
            is_string($handlerDef) && class_exists($handlerDef) => fn () => $this->invokeInvokableClass($handlerDef),
            is_callable($handlerDef) => fn () => $this->invokeCallable($handlerDef),
            is_array($handlerDef) && is_callable($handlerDef) => fn () => $this->invokeCallable($handlerDef),
            default => throw new \RuntimeException('Invalid route handler.'),
        };

        $result = $this->runner->run(
            $route->getMiddleware(),
            $controller,
            $request,
            $handler // ← the downstream handler
        );

        return $result;
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function invokeInvokableClass(string $class): mixed
    {
        $instance = $this->container->get($class);

        if (!is_callable($instance)) {
            throw new \RuntimeException("Handler class $class is not invokable.");
        }

        return $instance();
    }

    private function invokeCallable(callable $callable): mixed
    {
        return $callable();
    }
}
