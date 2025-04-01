<?php

declare(strict_types=1);

namespace Maduser\Argon\Kernel;

use Maduser\Argon\Console\Contracts\ConsoleInterface;
use Throwable;

final class CliKernel extends AbstractKernel
{
    public function __construct(
        private readonly ConsoleInterface $console
    ) {
    }

    public function handle(): void
    {
        try {
            exit($this->console->run());
        } catch (Throwable $e) {
            echo "Unhandled CLI Exception: {$e->getMessage()}\n";
            exit(1);
        }
    }
}
