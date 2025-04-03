<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Maduser\Argon\Container\ArgonContainer;
use RuntimeException;

/**
 * Wraps a route handler definition and executes it using the container's invoke logic.
 */
readonly class RouteHandler
{
    /**
     * @param ArgonContainer $container
     * @param array{0: string|object, 1?: string} $handler
     */
    public function __construct(
        private ArgonContainer $container,
        private array $handler
    ) {}

    /**
     * Invokes the underlying controller or callable with route parameters.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $args = $request->getAttributes();

        if (is_array($this->handler)) {
            [$classOrCallable, $method] = [$this->handler[0], $this->handler[1] ?? null];
            return $this->container->invoke($classOrCallable, $method, $args);
        }

        if (is_callable($this->handler)) {
            return $this->container->invoke($this->handler, null, $args);
        }

        throw new RuntimeException('Invalid route handler. Expected array or callable.');
    }

    /**
     * Returns the raw handler definition.
     *
     * @return callable|array{0: string|object, 1?: string}
     */
    public function getHandler(): callable|array
    {
        return $this->handler;
    }
}
