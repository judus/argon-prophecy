<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Routing\Contracts\RouterInterface;
use ReflectionException;

final class ArgonRouter implements RouterInterface
{
    private array $groupMiddleware = [];
    private string $groupPrefix = '';

    public function __construct(
        private readonly RouteCompiler $compiler
    ) {}

    /**
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function add(string $method, string $path, string|array|callable $handler, array $middleware = []): void
    {
        $method = strtoupper($method);

        $fullPath = rtrim($this->groupPrefix, '/') . '/' . ltrim($path, '/');

        $this->compiler->compile(
            $method,
            $fullPath,
            $handler,
            [...$this->groupMiddleware, ...$middleware]
        );
    }

    public function group(array $middleware, string $prefix, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix = rtrim($previousPrefix . '/' . trim($prefix, '/'), '/');
        $this->groupMiddleware = [...$previousMiddleware, ...$middleware];

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    public function get(string $path, string|array|callable $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string|array|callable $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, string|array|callable $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    public function patch(string $path, string|array|callable $handler, array $middleware = []): void
    {
        $this->add('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, string|array|callable $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    public function options(string $path, string|array|callable $handler, array $middleware = []): void
    {
        $this->add('OPTIONS', $path, $handler, $middleware);
    }
}
