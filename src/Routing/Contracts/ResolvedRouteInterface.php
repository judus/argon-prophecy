<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing\Contracts;

interface ResolvedRouteInterface
{
    /**
     * Returns the handler for this route.
     *
     * Can be:
     * - class-string
     * - callable
     * - [class-string, method-string]
     *
     * @return class-string|callable|array{0: class-string, 1: string}
     */
    public function getHandler(): string|callable|array;

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
    public function getParameters(): array;
}
