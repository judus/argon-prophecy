<?php

declare(strict_types=1);

namespace Maduser\Argon\Kernel\Exception;

use Maduser\Argon\Http\Factory\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final readonly class HttpExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(private ResponseFactory $responseFactory)
    {
    }

    public function report(Throwable $throwable): void
    {
        error_log((string) $throwable);
    }

    public function render(Throwable $throwable): ResponseInterface
    {
        // Keep it stupid-simple for dev mode
        return $this->responseFactory->text(
            sprintf(
                "Uncaught Exception: %s\n\n%s\n\n%s",
                $throwable::class,
                $throwable->getMessage(),
                $throwable->getTraceAsString()
            ),
            500
        );
    }
}
