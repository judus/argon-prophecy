<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Routing\Contracts\ResolvedRouteInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class RouteHandler
{
    public function __construct(
        private ArgonContainer $container,
        private string|object  $handler,
        private ?string        $method = null,
        private array $middleware = []
    ) {}

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function __invoke(ServerRequestInterface $request): mixed
    {
        $args = $request
            ->getAttribute(ResolvedRouteInterface::class)
            ?->getParameters() ?? [];

        return $this->container->invoke($this->handler, $this->method, $args);
    }

    public function getHandler(): string|object
    {
        return $this->handler;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}