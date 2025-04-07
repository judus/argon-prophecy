<?php
 namespace Maduser\Argon\Routing\Contracts;

use Maduser\Argon\Container\Exceptions\ContainerException;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

interface RouterInterface
{
/**
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function add(string $method, string $path, string|array|callable $handler, array $middleware = []): void;
    public function group(array $middleware, string $prefix, callable $callback): void;
    public function get(string $path, string|array|callable $handler, array $middleware = []): void;
    public function post(string $path, string|array|callable $handler, array $middleware = []): void;
    public function put(string $path, string|array|callable $handler, array $middleware = []): void;
    public function delete(string $path, string|array|callable $handler, array $middleware = []): void;
    public function patch(string $path, string|array|callable $handler, array $middleware = []): void;
    public function options(string $path, string|array|callable $handler, array $middleware = []): void;
}
