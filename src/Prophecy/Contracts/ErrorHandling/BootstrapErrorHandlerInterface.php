<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Contracts\ErrorHandling;

use Throwable;

interface BootstrapErrorHandlerInterface
{
    public function register(): void;
    public function handleException(Throwable $exception): void;
    /** @psalm-suppress PossiblyUnusedReturnValue */
    public function handleError(int $severity, string $message, string $file, int $line): bool;
    public function handleShutdown(): void;
}
