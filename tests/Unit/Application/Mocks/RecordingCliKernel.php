<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Mocks;

use Maduser\Argon\Contracts\Handler\CliKernelInterface;

final class RecordingCliKernel implements CliKernelInterface
{
    #[\Override]
    public function run(): int
    {
        return 0;
    }

    #[\Override]
    public function terminate(int $code = 0, bool $shouldExit = true): void
    {
        // no-op
    }
}
