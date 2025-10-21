<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Handler;

interface AppHandlerInterface
{
    /**
     * Execute the application lifecycle and return the exit code.
     */
    public function run(): int;

    /**
     * Terminate the current lifecycle.
     */
    public function terminate(int $code = 0, bool $shouldExit = true): void;
}
