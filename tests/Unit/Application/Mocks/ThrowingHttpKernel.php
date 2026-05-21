<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Mocks;

use Maduser\Argon\Contracts\Handler\HttpKernelInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class ThrowingHttpKernel implements HttpKernelInterface
{
    public ?ResponseInterface $emittedResponse = null;
    public bool $terminateCalled = false;

    #[\Override]
    public function handle(?ServerRequestInterface $request = null): void
    {
        throw new RuntimeException('kernel failure');
    }

    #[\Override]
    public function process(?ServerRequestInterface $request = null): ResponseInterface
    {
        throw new RuntimeException('kernel failure');
    }

    #[\Override]
    public function emit(ResponseInterface $response): void
    {
        $this->emittedResponse = $response;
    }

    #[\Override]
    public function run(): int
    {
        throw new RuntimeException('not used');
    }

    #[\Override]
    public function terminate(int $code = 0, bool $shouldExit = true): void
    {
        $this->terminateCalled = true;
    }
}
