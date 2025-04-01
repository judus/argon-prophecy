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

    public function add(string $method, string $path, string|array|callable $handler, array $middleware = []): void
    {
        $method = strtoupper($method);
        $this->routes[$method][] = [
            'path' => trim($path, '/'),
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function match(ServerRequestInterface $request): ResolvedRouteInterface
    {
        $method = strtoupper($request->getMethod());
        $uri = $request->getUri()->getPath();

        if (str_starts_with($uri, '/index.php')) {
            $uri = substr($uri, strlen('/index.php'));
        }

        $uri = '/' . ltrim($uri, '/');

        foreach ($this->routes[$method] ?? [] as $route) {
            $params = [];

            $routePath = '/' . ltrim($route['path'], '/'); // always start with /
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
