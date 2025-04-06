<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Middleware;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Routing\Contracts\MatchedRouteInterface;
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

        $serviceId = $route->getHandler();
        $invoker = $this->container->get($serviceId);

        if (!is_callable($invoker)) {
            throw new RuntimeException("Handler for route [{$serviceId}] is not callable.");
        }

        // Wrap it for lazy execution
        $callable = fn(): mixed => $invoker($route->getArguments());

        return $this->runner->run(
            $route->getMiddleware(),
            $callable,
            $request,
            $handler
        );
    }
}
