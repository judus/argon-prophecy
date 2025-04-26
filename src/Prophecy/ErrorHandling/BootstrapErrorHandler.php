<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\ErrorHandling;

use Closure;
use ErrorException;
use Maduser\Argon\Prophecy\Contracts\ErrorHandling\BootstrapErrorHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class BootstrapErrorHandler implements BootstrapErrorHandlerInterface
{
    private ?LoggerInterface $logger;
    private Closure $outputCallback;
    private Closure $terminateCallback;
    private Closure $errorGetLastCallback;
    private string $sapi;
    private $stream;


    public function __construct(
        ?LoggerInterface $logger = null,
        ?Closure $outputCallback = null,
        ?Closure $terminateCallback = null,
        ?Closure $errorGetLastCallback = null,
        ?string $sapi = null,
        $stream = null
    ) {
        $this->logger = $logger;
        $this->sapi = $sapi ?? PHP_SAPI;
        $this->stream = $stream;
        $this->outputCallback = $outputCallback ?? $this->defaultOutputCallback();
        $this->terminateCallback = $terminateCallback ?? static function (int $code): void {
            exit($code); // @codeCoverageIgnore
        };
        $this->errorGetLastCallback = $errorGetLastCallback ?? static fn() => error_get_last();
    }

    private function defaultOutputCallback(): Closure
    {
        return function (string $message): void {
            $stream = $this->stream ?? STDERR;

            if ($this->sapi === 'cli') {
                fwrite($stream, $message);
            } else {
                http_response_code(500);
                echo '<pre>' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
            }
        };
    }

    public function register(): void
    {
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleError(int $severity, string $message, string $file, int $line): bool
    {
        $exception = new ErrorException($message, 0, $severity, $file, $line);
        $this->handleException($exception);
        return true; // @codeCoverageIgnore
    }

    public function handleException(Throwable $exception): void
    {
        $this->log($exception);
        ($this->outputCallback)($this->formatMessage($exception));
        ($this->terminateCallback)(1);
    }

    public function handleShutdown(): void
    {
        $error = ($this->errorGetLastCallback)();
        if ($error !== null) {
            $this->handleException(new ErrorException(
                $error['message'] ?? 'Unknown fatal error',
                0,
                $error['type'] ?? E_ERROR,
                $error['file'] ?? 'unknown',
                $error['line'] ?? 0
            ));
        }
    }

    private function log(Throwable $exception): void
    {
        $this->logger?->error('Unhandled bootstrap exception', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    private function formatMessage(Throwable $exception): string
    {
        return sprintf(
            "Fatal error: %s in %s:%d\n",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
    }
}
