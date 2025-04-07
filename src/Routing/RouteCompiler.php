<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Support\ReflectionUtils;
use Maduser\Argon\Container\Support\ServiceInvoker;
use Maduser\Argon\Container\Exceptions\ContainerException;
use ReflectionException;

final readonly class RouteCompiler
{
    public function __construct(
        private ArgonContainer $container
    ) {}

    /**
     * Compiles and registers a route handler and its middleware into the container.
     *
     * @param string $method HTTP method, e.g. 'GET'
     * @param string $path Route path, used as service ID
     * @param string|array|callable $handler Controller class or [class, method]
     * @param string[] $middlewares Middleware service IDs
     *
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function compile(
        string $method,
        string $path,
        string|array|callable $handler,
        array $middlewares = []
    ): void {
        $methodName = '__invoke';

        if (is_array($handler)) {
            $class = $handler[0];
            $methodName = $handler[1] ?? '__invoke';
        } else {
            $class = $handler;
        }

        $args = ReflectionUtils::getMethodParameters($class, $methodName);

        $descriptor = $this->container->has($class)
            ? $this->container->getDescriptor($class)
            : $this->container->bind($class, $class, false, []);

        $descriptor->setMethod($methodName, $args);

        $normalizedPath = '/' . ltrim($path, '/');
        $normalizedMethod = strtolower($method);
        $middlewareTag = "route.middleware." . strtolower($normalizedPath);
        $routeTag = "route.$normalizedMethod";

        // Register invoker for the route
        $this->container->bind($normalizedPath, ServiceInvoker::class, args: [
            'serviceId' => $class,
            'method' => $methodName,
        ])->tag($routeTag);

        // Register each middleware under its own middleware tag
        foreach ($middlewares as $middleware) {
            $this->container->bind($middleware)->tag($middlewareTag);
        }
    }
}
