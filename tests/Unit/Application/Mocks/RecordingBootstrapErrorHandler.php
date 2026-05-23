<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Mocks;

use Maduser\Argon\Prophecy\Contracts\ErrorHandling\BootstrapErrorHandlerInterface;
use Maduser\Argon\Prophecy\ErrorHandling\BootstrapErrorHandlerMode;
use Throwable;

final class RecordingBootstrapErrorHandler implements BootstrapErrorHandlerInterface
{
    public int $registerCount = 0;
    public int $unregisterCount = 0;
    public ?Throwable $lastException = null;
    public ?BootstrapErrorHandlerMode $outputMode = null;

    #[\Override]
    public function register(): void
    {
        $this->registerCount++;
    }

    #[\Override]
    public function unregister(): void
    {
        $this->unregisterCount++;
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

    public function setOutputMode(BootstrapErrorHandlerMode $mode): void
    {
        $this->outputMode = $mode;
    }
}
