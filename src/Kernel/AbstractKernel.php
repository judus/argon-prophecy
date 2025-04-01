<?php

declare(strict_types=1);

namespace Maduser\Argon\Kernel;

use Maduser\Argon\Kernel\Contracts\KernelInterface;

abstract class AbstractKernel implements KernelInterface
{
    public function setup(): void
    {
        // Register global exception handlers
    }

    public function boot(): void
    {
        // Load routes, preload stuff, etc.
    }

    public function terminate(): void
    {
        // Session cleanup, flush logs, etc.
    }
}
