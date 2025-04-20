<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Exception;

use Maduser\Argon\Contracts\Http\Exception\ExceptionHandlerInterface;
use Maduser\Argon\Exception\ExceptionDispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class ExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(
        private ExceptionDispatcher $dispatcher,
        private LoggerInterface $logger
    ) {
    }

    public function handle(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->error('Unhandled exception', [
            'exception' => $e,
        ]);

        try {
            return $this->dispatcher->dispatch($e, $request);
        } catch (Throwable $fallback) {
            return $this->dispatcher->dispatch($fallback, $request);
        }
    }
}
