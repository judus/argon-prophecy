<?php

declare(strict_types=1);

namespace Maduser\Argon\Console;

use Maduser\Argon\Console\Exception\ExceptionHandler;
use Maduser\Argon\Contracts\Console\ConsoleInterface;
use Maduser\Argon\Contracts\KernelInterface;
use Throwable;

final readonly class CliKernel implements KernelInterface
{
    public function __construct(
        private ConsoleInterface $console,
        private ExceptionHandler $exceptionHandler,
    ) {
    }

    public function handle(): void
    {
        try {
            exit($this->console->run());
        } catch (Throwable $e) {
            exit($this->exceptionHandler->handle($e));
        }
    }
}
