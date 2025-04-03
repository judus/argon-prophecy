<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing\Adapters;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Routing\Contracts\RouterInterface;
use Maduser\Argon\Routing\Contracts\ResolvedRouteInterface;
use Maduser\Argon\Routing\MatchedRoute;
use Maduser\Argon\Routing\RouteHandler;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class ArgonRouteAdapter implements RouterInterface
{
    public function __construct(
        private readonly ArgonContainer $container
    ) {
    }

    /** @var array<string, list<array{path: string, handler: string|callable, middleware: list<string>}>> */
    private array $routes = [];

    private array $groupMiddleware = [];
    private string $groupPrefix = '';

    /**
     * @throws ContainerException
     */
    public function add(string $method, string $path, string|array|callable $handler, array $middleware = []): void
    {
        $method = strtoupper($method);

        $fullPath = rtrim($this->groupPrefix, '/') . '/' . ltrim($path, '/');

        $this->routes[$method][] = [
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => [...$this->groupMiddleware, ...$middleware],
        ];

        $this->registerRoute($method, $fullPath, $handler, $middleware);
    }

    /**
     * @throws ContainerException
     */
    private function registerRoute(string $method, string $path, callable|array|string $handler, array $middleware = []): void
    {
        $tag = 'route.' . strtolower($method);

        $args = [];

        if (is_array($handler)) {
            $args = [
                'handler' => $handler[0],
                'method' => $handler[1] ?? '__invoke',
                'middleware' => $middleware,
            ];
        } else {
            $args = [
                'handler' => $handler,
                'method' => '__invoke',
                'middleware' => $middleware,
            ];
        }

        $this->container->getArgumentMap()->set($path, $args);

        $this->container
            ->singleton($path, RouteHandler::class)
            ->tag([$tag]);

    }

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

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function match(ServerRequestInterface $request): ResolvedRouteInterface
    {
        // Merge compiled/container-defined routes (tagged services) with local $this->routes
        $method = strtoupper($request->getMethod());
        $uri = $this->stripIndex($request->getUri()->getPath());
        $uri = '/' . ltrim($uri, '/');

        $compiledRoutes = $this->container->getTaggedIds('route.' . strtolower($method));

        // Add container-defined routes to internal route map (lazy-resolving)
        foreach ($compiledRoutes as $routePath) {
            $routePath = trim($routePath, '/');
            $this->routes[$method][] = [
                'path' => $routePath,
                'handler' => null, // will resolve at match-time via container
                'middleware' => [],
            ];
        }

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

                $handler = $route['handler'] ?? $this->container->get($routePath);

                return new MatchedRoute(
                    handler: $handler,
                    middleware: $handler->getMiddleware() ?? $route['middleware'],
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
