<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Contracts;

use Maduser\Argon\Middleware\MiddlewareDefinition;

interface MiddlewareLoaderInterface
{
    /** @return array<string, list<MiddlewareDefinition>> */
    public function loadGrouped(): array;
}
