<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing\Adapters;

use Maduser\Argon\Routing\Contracts\RouterInterface;
use Maduser\Argon\Routing\Contracts\ResolvedRouteInterface;
use Maduser\Argon\Routing\MatchedRoute;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class ArgonRouteAdapter implements RouterInterface
{
    /** @var array<string, list<array{path: string, handler: string|callable, middleware: list<string>}>> */
    private array $routes = [];

    private array $groupMiddleware = [];
    private string $groupPrefix = '';

    // Core route registration method
    public function add(string $method, string $path, string|array|callable $handler, array $middleware = []): void
    {
        $method = strtoupper($method);

        $fullPath = trim($this->groupPrefix . '/' . trim($path, '/'), '/');

        $this->routes[$method][] = [
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => [...$this->groupMiddleware, ...$middleware],
        ];
    }

    // Route group handling with prefix and shared middleware
    public function group(array $middleware, string $prefix, callable $callback): void
    {
        $prevPrefix = $this->groupPrefix;
        $prevMiddleware = $this->groupMiddleware;

        $this->groupPrefix = rtrim($prevPrefix . '/' . trim($prefix, '/'), '/');
        $this->groupMiddleware = [...$prevMiddleware, ...$middleware];

        $callback($this);

        $this->groupPrefix = $prevPrefix;
        $this->groupMiddleware = $prevMiddleware;
    }

    // HTTP verb shortcut methods
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

    public function delete(string $path, string|array|callable $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    public function patch(string $path, string|array|callable $handler, array $middleware = []): void
    {
        $this->add('PATCH', $path, $handler, $middleware);
    }

    public function options(string $path, string|array|callable $handler, array $middleware = []): void
    {
        $this->add('OPTIONS', $path, $handler, $middleware);
    }

    // Route matching implementation
    public function match(ServerRequestInterface $request): ResolvedRouteInterface
    {
        $method = strtoupper($request->getMethod());
        $uri = $request->getUri()->getPath();

        $uri = $this->stripIndex($uri);
        $uri = '/' . ltrim($uri, '/');

        foreach ($this->routes[$method] ?? [] as $route) {
            $params = [];
            $routePath = '/' . ltrim($route['path'], '/');

            $regex = '#^' . preg_replace_callback('/{(\w+)}/', function ($m) {
                    return '(?P<' . $m[1] . '>[^/]+)';
                }, $routePath) . '$#';

            if (preg_match($regex, $uri, $matches)) {
                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $params[$key] = $value;
                    }
                }

                return new MatchedRoute(
                    handler: $route['handler'],
                    middleware: $route['middleware'] ?? [],
                    parameters: $params
                );
            }
        }

        throw new RuntimeException("No route matched: {$method} {$uri}");
    }

    private function stripIndex(string $path): string
    {
        return preg_replace('#^/?index\.php#', '', $path) ?? $path;
    }
}
