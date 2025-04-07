<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Routing\Contracts\MatchedRouteInterface;
use Maduser\Argon\Routing\Contracts\RouteMatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final readonly class RouteMatcher implements RouteMatcherInterface
{
    public function __construct(
        private ArgonContainer $container
    ) {
    }

    public function match(ServerRequestInterface $request): MatchedRouteInterface
    {
        $method = strtoupper($request->getMethod());
        $uri = $this->normalizeUri($request->getUri()->getPath());
        $routeTag = "route." . strtolower($method);

        foreach ($this->container->getTaggedIds($routeTag) as $routePath) {
            $routePath = $this->normalizeRoute($routePath);
            $pattern = $this->compileRoutePattern($routePath);
            $middlewareTag = "route.middleware." . strtolower($routePath);

            if (preg_match($pattern, $uri, $matches)) {
                return new MatchedRoute(
                    handler: $routePath,
                    middleware: $this->container->getTaggedIds($middlewareTag),
                    arguments: $this->extractParams($matches)
                );
            }
        }

        throw new RuntimeException("No route matched: {$method} {$uri}");
    }

    private function normalizeUri(string $uri): string
    {
        $uri = preg_replace('#^/?index\.php#', '', $uri) ?? $uri;
        return '/' . ltrim($uri, '/');
    }

    private function normalizeRoute(string $route): string
    {
        return '/' . trim($route, '/');
    }

    private function compileRoutePattern(string $route): string
    {
        return '#^' . preg_replace_callback('/{(\w+)}/', fn($m) => '(?P<' . $m[1] . '>[^/]+)', $route) . '$#';
    }

    private function extractParams(array $matches): array
    {
        return array_filter(
            $matches,
            fn($key) => !is_int($key),
            ARRAY_FILTER_USE_KEY
        );
    }
}
