<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Mocks;

use GuzzleHttp\Psr7\Response;
use Maduser\Argon\Contracts\Handler\HttpKernelInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class EmitFailingHttpKernel implements HttpKernelInterface
{
    public bool $terminateCalled = false;

    #[\Override]
    public function handle(?ServerRequestInterface $request = null): void
    {
        // no-op
    }

    #[\Override]
    public function process(?ServerRequestInterface $request = null): ResponseInterface
    {
        return new Response();
    }

    #[\Override]
    public function emit(ResponseInterface $response): void
    {
        throw new RuntimeException('emit failure');
    }

    #[\Override]
    public function run(): int
    {
        return 0;
    }

    #[\Override]
    public function terminate(int $code = 0, bool $shouldExit = true): void
    {
        $this->terminateCalled = true;
    }
}
