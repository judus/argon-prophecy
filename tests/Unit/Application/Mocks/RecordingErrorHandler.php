<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Mocks;

use Maduser\Argon\Support\Contracts\ErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class RecordingErrorHandler implements ErrorHandlerInterface
{
    public int $registerCount = 0;
    public ?Throwable $lastException = null;
    public ?ServerRequestInterface $lastRequest = null;

    public function __construct(private readonly ResponseInterface $response)
    {
    }

    #[\Override]
    public function register(): void
    {
        $this->registerCount++;
    }

    #[\Override]
    public function handle(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        $this->lastException = $e;
        $this->lastRequest = $request;

        return $this->response;
    }
}
