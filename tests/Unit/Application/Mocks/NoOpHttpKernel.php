<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Mocks;

use Maduser\Argon\Contracts\Handler\HttpKernelInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class NoOpHttpKernel implements HttpKernelInterface
{
    public bool $handleCalled = false;

    #[\Override]
    public function handle(?ServerRequestInterface $request = null): void
    {
        $this->handleCalled = true;
    }

    #[\Override]
    public function process(?ServerRequestInterface $request = null): ResponseInterface
    {
        throw new RuntimeException('process() should not be called during this test.');
    }

    #[\Override]
    public function emit(ResponseInterface $response): void
    {
        // no-op
    }

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
