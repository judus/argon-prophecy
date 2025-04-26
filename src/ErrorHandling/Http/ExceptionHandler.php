<?php

declare(strict_types=1);

namespace Maduser\Argon\ErrorHandling\Http;

use ErrorException;
use Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionDispatcherInterface;
use Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionHandlerInterface;
use Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionFormatterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class ExceptionHandler implements ExceptionHandlerInterface
{
    private bool $registered = false;

    public function __construct(
        private readonly ExceptionDispatcherInterface $dispatcher,
        private readonly ExceptionFormatterInterface $formatter,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $this->registered = true;

        set_error_handler($this->createErrorHandler());
        set_exception_handler($this->createExceptionHandler());
        register_shutdown_function([$this, 'shutdownFunction']);
    }

    public function handle(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        try {
            return $this->dispatchException($e, $request);
        } catch (Throwable $fallback) {
            return $this->handleFallbackException($e, $fallback, $request);
        }
    }

    private function dispatchException(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        return $this->dispatcher->dispatch($e, $request);
    }

    private function handleFallbackException(
        Throwable $original,
        Throwable $fallback,
        ServerRequestInterface $request
    ): ResponseInterface {
        $this->logger?->critical('Dispatcher failure during exception handling', [
            'original_exception' => $original,
            'fallback_exception' => $fallback,
        ]);

        return $this->formatter->format($fallback, $request);
    }

    private function createErrorHandler(): callable
    {
        return function (
            int $severity,
            string $message,
            string $file,
            int $line
        ): bool {
            try {
                throw new ErrorException($message, 0, $severity, $file, $line);
            } catch (Throwable $e) {
                $this->logger?->critical('Error converted to Exception', ['exception' => $e]);
            }

            return true;
        };
    }

    private function createExceptionHandler(): callable
    {
        return function (Throwable $e): void {
            $this->logger?->critical('Unhandled throwable', ['exception' => $e]);
        };
    }

    private function shutdownFunction(?array $error = null): void
    {
        $error ??= error_get_last();
        if ($this->isFatalError($error)) {
            $throwable = new ErrorException(
                $error['message'] ?? 'Fatal error',
                0,
                $error['type'] ?? E_ERROR,
                $error['file'] ?? 'unknown',
                $error['line'] ?? 0
            );
            $this->logger?->critical('Fatal shutdown error', ['exception' => $throwable]);
        }
    }

    private function isFatalError(?array $error): bool
    {
        return $error !== null && in_array($error['type'], [
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_COMPILE_ERROR,
            ], true);
    }
}
