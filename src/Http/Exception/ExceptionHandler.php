<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Exception;

use ErrorException;
use Maduser\Argon\Contracts\Http\Exception\ExceptionHandlerInterface;
use Maduser\Argon\Exception\ExceptionDispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class ExceptionHandler implements ExceptionHandlerInterface
{
    private bool $registered = false;

    public function __construct(
        private readonly ExceptionDispatcher $dispatcher,
        private readonly LoggerInterface $logger
    ) {}

    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $this->registered = true;

        set_error_handler(function (
            int $severity,
            string $message,
            string $file,
            int $line
        ): bool {
            try {
                throw new ErrorException($message, 0, $severity, $file, $line);
            } catch (Throwable $e) {
                $this->logger->critical('Exception during error_handler', ['exception' => $e]);
            }
            return true;
        });

        set_exception_handler(function (Throwable $e): void {
            $this->logger->critical('Unhandled throwable', ['exception' => $e]);
            // Let Kernel still handle shutdown response.
        });

        register_shutdown_function(function (): void {
            $error = error_get_last();
            if ($this->isFatalError($error)) {
                $throwable = new ErrorException(
                    $error['message'] ?? 'Fatal error',
                    0,
                    $error['type'] ?? E_ERROR,
                    $error['file'] ?? 'unknown',
                    $error['line'] ?? 0
                );
                $this->logger->critical('Fatal shutdown error', ['exception' => $throwable]);
            }
        });
    }

    public function handle(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->error('Unhandled exception', ['exception' => $e]);

        try {
            return $this->dispatcher->dispatch($e, $request);
        } catch (Throwable $fallback) {
            $this->logger->critical('Exception handler failed', ['exception' => $fallback]);
            return $this->dispatcher->dispatch($fallback, $request);
        }
    }

    private function isFatalError(?array $error): bool
    {
        return $error !== null && in_array($error['type'], [
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_COMPILE_ERROR
            ], true);
    }
}
