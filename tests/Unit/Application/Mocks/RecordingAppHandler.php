<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Mocks;

use Maduser\Argon\Contracts\Handler\AppHandlerInterface;

final class RecordingAppHandler implements AppHandlerInterface
{
    public bool $runCalled = false;
    public ?int $terminateCode = null;
    public ?bool $terminateShouldExit = null;

    public function __construct(private readonly int $exitCode = 0)
    {
    }

    #[\Override]
    public function run(): int
    {
        $this->runCalled = true;

        return $this->exitCode;
    }

    #[\Override]
    public function terminate(int $code = 0, bool $shouldExit = true): void
    {
        $this->terminateCode = $code;
        $this->terminateShouldExit = $shouldExit;
    }
}
