<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing\Contracts;

use Closure;

interface MatchedRouteInterface
{
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
    public function getHandler(): string|array|Closure;

    public function getMethod(): string;

    /**
     * Returns middleware service IDs for this route.
     *
     * @return list<class-string> PSR-15 middleware service IDs
     */
    public function getMiddleware(): array;

    /**
     * Returns route parameters extracted from the URI.
     *
     * @return array<string, scalar>
     */
    public function getArguments(): array;
}
