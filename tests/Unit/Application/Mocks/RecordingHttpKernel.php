<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Mocks;

use GuzzleHttp\Psr7\Response;
use Maduser\Argon\Contracts\Handler\HttpKernelInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RecordingHttpKernel implements HttpKernelInterface
{
    public ?ServerRequestInterface $processedRequest = null;
    public ?ResponseInterface $emittedResponse = null;
    public ?int $terminateCode = null;

    public function __construct(private readonly ResponseInterface $response = new Response())
    {
    }

    #[\Override]
    public function handle(?ServerRequestInterface $request = null): void
    {
        // no-op
    }

    #[\Override]
    public function process(?ServerRequestInterface $request = null): ResponseInterface
    {
        $this->processedRequest = $request;

        return $this->response;
    }

    #[\Override]
    public function emit(ResponseInterface $response): void
    {
        $this->emittedResponse = $response;
    }

    #[\Override]
    public function run(): int
    {
        return 0;
    }

    #[\Override]
    public function terminate(int $code = 0, bool $shouldExit = true): void
    {
        $this->terminateCode = $code;
    }
}
