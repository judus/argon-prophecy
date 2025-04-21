<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Loader;

use Maduser\Argon\Middleware\Contracts\MiddlewareLoaderInterface;
use Maduser\Argon\Middleware\MiddlewareDefinition;

final class StaticMiddlewareLoader implements MiddlewareLoaderInterface
{
    /** @var array<string, list<MiddlewareDefinition>> */
    private array $grouped;

    /**
     * @param array<string, list<MiddlewareDefinition>> $grouped
     */
    public function __construct(array $grouped)
    {
        $this->grouped = $grouped;
    }

    public function loadGrouped(): array
    {
        return $this->grouped;
    }
}
