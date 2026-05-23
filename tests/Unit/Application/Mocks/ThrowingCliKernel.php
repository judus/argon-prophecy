<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Mocks;

use Maduser\Argon\Contracts\Handler\CliKernelInterface;
use RuntimeException;

final class ThrowingCliKernel implements CliKernelInterface
{
    public ?int $terminateCode = null;

    #[\Override]
    public function run(): int
    {
        throw new RuntimeException('cli failure');
    }

    #[\Override]
    public function terminate(int $code = 0, bool $shouldExit = true): void
    {
        $this->terminateCode = $code;
    }
}
