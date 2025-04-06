<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use Closure;
use Maduser\Argon\Routing\Contracts\MatchedRouteInterface;

/**
 * Immutable value object representing a resolved route match.
 */
final readonly class MatchedRoute implements MatchedRouteInterface
{
    /**
     * @param class-string|array{0: class-string, 1: string}|Closure $handler
     * @param list<class-string> $middleware
     * @param array<string, scalar> $arguments
     */
    public function __construct(
        private string|array|Closure $handler,
        private string $method = '__invoke',
        private array $middleware = [],
        private array $arguments = []
    ) {
    }

    /**
     * Returns the handler for this route.
     *
     * Can be:
     * - class-string
     * - callable
     * - [class-string, method-string]
     *
     * @return class-string|array{0: class-string, 1: string}|Closure
     */
    public function getHandler(): string|array|Closure
    {
        return $this->handler;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return list<class-string>
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @return array<string, scalar>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
