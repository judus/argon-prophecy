<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Mocks;

use Maduser\Argon\Prophecy\Contracts\ErrorHandling\BootstrapErrorHandlerInterface;
use Throwable;

final class MinimalBootstrapErrorHandler implements BootstrapErrorHandlerInterface
{
    public ?Throwable $lastException = null;

    #[\Override]
    public function register(): void
    {
        // no-op
    }

    #[\Override]
    public function unregister(): void
    {
        // no-op
    }

    #[\Override]
    public function handleException(Throwable $exception): void
    {
        $this->lastException = $exception;
    }

    #[\Override]
    public function handleError(int $severity, string $message, string $file, int $line): bool
    {
        return true;
    }

    #[\Override]
    public function handleShutdown(): void
    {
        // no-op
    }
}
