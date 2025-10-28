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

    /**
     * @var resource|null
     */
    private $stream;

    /**
     * @param resource|null $stream
     */
    public function __construct(
        ?LoggerInterface $logger = null,
        ?Closure $outputCallback = null,
        ?Closure $terminateCallback = null,
        ?Closure $errorGetLastCallback = null,
        ?string $sapi = null,
        $stream = null
    ) {
        $this->logger = $logger;
        $this->sapi = $sapi ?? php_sapi_name();
        $this->stream = $stream;
        $this->outputCallback = $outputCallback ?? $this->defaultOutputCallback();
        $this->terminateCallback = $terminateCallback ?? static function (int $code): void {
            exit($code); // @codeCoverageIgnore
        };
        $this->errorGetLastCallback = $errorGetLastCallback ?? static fn(): array|null => error_get_last();
    }

    private function defaultOutputCallback(): Closure
    {
        return function (string $message): void {
            $stream = $this->stream ?? fopen('php://stderr', 'w');

            $isCliSapi = $this->isCliSapi();
            $isCliServer = $this->isCliServer();

            if ($isCliSapi || $isCliServer) {
                fwrite($stream, $message);
            }

            if ($this->isCliServingHttp()) {
                http_response_code(500);
                if (!headers_sent()) {
                    header('Content-Type: text/plain; charset=UTF-8');
                }
                echo $message;
                return;
            }

            if (!$isCliSapi && !$isCliServer) {
                http_response_code(500);
                echo '<pre>' . htmlspecialchars($message, \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
            }
        };
    }

    public function register(): void
    {
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleException(Throwable $exception): void
    {
        $this->log($exception);
        ($this->outputCallback)($this->formatMessage($exception));
        ($this->terminateCallback)(1);
    }

    /** @psalm-suppress PossiblyUnusedReturnValue */
    public function handleError(int $severity, string $message, string $file, int $line): bool
    {
        $exception = new ErrorException($message, 0, $severity, $file, $line);
        $this->handleException($exception);
        return true; // @codeCoverageIgnore
    }

    public function handleShutdown(): void
    {
        /** @var array{type: int, message: string, file: string, line: int}|null $error */
        $error = ($this->errorGetLastCallback)();
        if ($error !== null) {
            $this->handleException(new ErrorException(
                $error['message'] ?? 'Unknown fatal error',
                0,
                $error['type'] ?? \E_ERROR,
                $error['file'] ?? 'unknown',
                $error['line'] ?? 0
            ));
        }
    }

    private function log(Throwable $exception): void
    {
        $origin = $this->resolveOrigin($exception);

        $this->logger?->error('Unhandled bootstrap exception', [
            'message' => $exception->getMessage(),
            'file' => $origin->getFile(),
            'line' => $origin->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    private function formatMessage(Throwable $exception): string
    {
        $origin = $this->resolveOrigin($exception);
        $className = $exception::class;
        $exceptionName = str_contains($className, '\\')
            ? substr(strrchr($className, '\\'), 1)
            : $className;

        return sprintf(
            "Fatal error: %s\n\nMessage: %s\n\nLocation: %s:%d\n\nTrace:\n%s\n",
            $exceptionName,
            $exception->getMessage(),
            $origin->getFile(),
            $origin->getLine(),
            $exception->getTraceAsString()
        );
    }

    private function resolveOrigin(Throwable $exception): Throwable
    {
        return $exception->getPrevious() ?? $exception;
    }

    private function isCliServingHttp(): bool
    {
        if ($this->isCliServer()) {
            return true;
        }

        return $this->isCliSapi()
            && isset($_SERVER['REQUEST_METHOD']);
    }

    private function isCliSapi(): bool
    {
        return $this->sapi === 'cli' || $this->sapi === 'phpdbg';
    }

    private function isCliServer(): bool
    {
        return $this->sapi === 'cli-server';
    }
}
