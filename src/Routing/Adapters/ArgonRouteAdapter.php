<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing\Adapters;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Container\Support\ReflectionUtils;
use Maduser\Argon\Container\Support\ServiceInvoker;
use Maduser\Argon\Routing\Contracts\RouterInterface;
use Maduser\Argon\Routing\Contracts\MatchedRouteInterface;
use Maduser\Argon\Routing\MatchedRoute;
use Maduser\Argon\Routing\RouteHandler;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use ReflectionMethod;
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
     * @throws ReflectionException
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
     * @throws ReflectionException
     */
    private function registerRoute(string $method, string $path, callable|array|string $handler, array $middlewares = []): void
    {
        $methodName = '__invoke';

        // Determine controller and method name
        if (is_array($handler)) {
            $class = $handler[0];
            $methodName = $handler[1] ?? '__invoke';
        } else {
            $class = $handler;
        }

        // Reflect and generate argument map
        $args = ReflectionUtils::getMethodParameters($class, $methodName);

        // Set method parameter map on descriptor
        if ($this->container->has($class)) {
            $descriptor = $this->container->getDescriptor($class);
            $descriptor?->setMethod($methodName, $args);
        } else {
            $this->container->bind($class, $class, false, [])
                ->setMethod($methodName, $args);
        }

        $path = $this->normalizePath($path);

        $this->container->bind($path, ServiceInvoker::class, args: [
            'serviceId' => $class,
            'method'    => $methodName,
        ])
            ->tag('route.' . strtolower($method));

        foreach ($middlewares as $middleware) {
            $this->container->bind($middleware)->tag("route.$path");
        }
    }

    public function normalizePath(string $path): string {
        return '/' . ltrim($path, '/');
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

    public function match(ServerRequestInterface $request): MatchedRouteInterface
    {
        $method = strtoupper($request->getMethod());
        $uri = '/' . ltrim($this->stripIndex($request->getUri()->getPath()), '/');

        // Load container-defined (compiled) routes lazily into $this->routes
        $compiledRoutes = $this->container->getTaggedIds('route.' . strtolower($method));

        foreach ($compiledRoutes as $routePath) {
            $routePath = trim($routePath, '/');
            if (!isset($this->routes[$method]) || !in_array($routePath, array_column($this->routes[$method], 'path'), true)) {
                $this->routes[$method][] = [
                    'path' => $routePath,
                    'handler' => null,
                    'middleware' => [],
                ];
            }
        }

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

                $key = $this->normalizePath($route['path']);

                return new MatchedRoute(
                    handler: $this->normalizePath($key),
                    middleware: $this->container->getTaggedIds("route.$key"),
                    arguments: $params
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
