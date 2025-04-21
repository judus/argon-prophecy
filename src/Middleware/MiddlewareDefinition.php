<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware;

class MiddlewareDefinition
{
    public const DEFAULT_GROUP = '__ungrouped';

    public function __construct(
        public readonly string $class,
        public readonly int $priority = 0
    ) {
    }
}
