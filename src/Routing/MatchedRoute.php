<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use Maduser\Argon\Routing\Contracts\ResolvedRouteInterface;

/**
 * Immutable value object representing a resolved route match.
 */
final readonly class MatchedRoute implements ResolvedRouteInterface
{
    /**
     * @param class-string|callable|array{0: class-string, 1: string} $handler
     * @param list<class-string> $middleware
     * @param array<string, scalar> $parameters
     */
    public function __construct(
        private mixed $handler,
        private array $middleware = [],
        private array $parameters = []
    ) {}

    public function getHandler(): string|callable|array
    {
        return $this->handler;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
