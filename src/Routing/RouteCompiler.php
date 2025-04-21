<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Support\ReflectionUtils;
use Maduser\Argon\Container\Support\ServiceInvoker;
use Maduser\Argon\Middleware\Contracts\PipelineStoreInterface;
use Maduser\Argon\Middleware\MiddlewareStack;
use Maduser\Argon\Routing\Contracts\RouteCompilerInterface;
use ReflectionException;

final readonly class RouteCompiler implements RouteCompilerInterface
{
    public function __construct(
        private ArgonContainer         $container,
        private PipelineStoreInterface $pipelineStore,
    ) {
    }

    /**
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
            : $this->container->set($class, $class, false, []);

        $descriptor->defineInvocation($methodName, $args);

        $normalizedPath = '/' . ltrim($path, '/');
        $normalizedMethod = strtolower($method);
        $routeTag = "route.$normalizedMethod";

        $stack = $this->prepareMiddlewareStack($middlewares);

        // 1. Register controller invoker for the route
        $this->container->set($normalizedPath, ServiceInvoker::class, args: [
            'serviceId' => $class,
            'method' => $methodName,
        ])->tag([
            $routeTag => [
                'pipeline'   => $stack->getId(),
                'middleware' => $stack->toArray(),
            ]
        ]);

        // 2. Register pipeline immediately
        $this->pipelineStore->register($stack);
    }

    private function prepareMiddlewareStack(array $middlewares): MiddlewareStack
    {
        if (empty($middlewares)) {
            return new MiddlewareStack([]);
        }

        $meta = $this->container->getTaggedMeta('middleware.http');

        $expanded = $this->expandGroupAliases($middlewares, $meta);
        return $this->buildSortedStack($expanded, $meta);
    }

    /**
     * @param array<string> $input
     * @param array<string, array> $meta
     * @return array<string>
     */
    private function expandGroupAliases(array $input, array $meta): array
    {
        $expanded = [];

        foreach ($input as $alias) {
            foreach ($meta as $class => $attributes) {
                $groups = [];

                if (isset($attributes['group'])) {
                    $groups = is_array($attributes['group'])
                        ? $attributes['group']
                        : array_map('trim', explode(',', (string) $attributes['group']));
                }

                if (in_array($alias, $groups, true)) {
                    $expanded[] = $class;
                }
            }
        }

        return $expanded !== [] ? array_unique($expanded) : $input;
    }

    /**
     * @param array<string> $middleware
     * @param array<string, array> $meta
     */
    private function buildSortedStack(array $middleware, array $meta): MiddlewareStack
    {
        $withPriority = [];

        foreach ($middleware as $class) {
            $priority = $meta[$class]['priority'] ?? 0;
            $withPriority[] = ['class' => $class, 'priority' => (int) $priority];
        }

        usort($withPriority, fn ($a, $b) => $b['priority'] <=> $a['priority']);

        return new MiddlewareStack(array_column($withPriority, 'class'));
    }
}
