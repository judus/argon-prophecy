<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing\Contracts;

use Closure;

interface MatchedRouteInterface
{
    /**
     * @return class-string|array{0: class-string, 1: string}|Closure
     */
    public function getHandler(): string|array|Closure;

    public function getMethod(): string;

    /**
     * @return list<class-string>
     */
    public function getMiddleware(): array;

    /**
     * @return array<string, scalar>
     */
    public function getArguments(): array;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;

    public function __toString(): string;
}
