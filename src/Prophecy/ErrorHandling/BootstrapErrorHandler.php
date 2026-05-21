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
    /**
     * @var list<int>
     */
    private const FATAL_ERROR_TYPES = [
        \E_ERROR,
        \E_PARSE,
        \E_CORE_ERROR,
        \E_COMPILE_ERROR,
        \E_USER_ERROR,
        \E_RECOVERABLE_ERROR,
    ];

    private ?LoggerInterface $logger;
    private Closure $outputCallback;
    private Closure $terminateCallback;
    private Closure $errorGetLastCallback;
    private string $sapi;
    private ?BootstrapErrorHandlerMode $mode = null;
    private bool $registered = false;

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
            if ($stream === false) {
                return;
            }

            $mode = $this->resolveMode();

            switch ($mode) {
                case BootstrapErrorHandlerMode::CLI:
                    fwrite($stream, $message);
                    break;

                case BootstrapErrorHandlerMode::CLI_SERVER:
                    http_response_code(500);
                    if (!headers_sent()) {
                        header('Content-Type: text/plain; charset=UTF-8');
                    }
                    echo $message;
                    break;

                case BootstrapErrorHandlerMode::HTTP:
                    http_response_code(500);
                    echo '<pre>' . htmlspecialchars($message, \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
                    break;
            }
        };
    }

    #[\Override]
    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleShutdown']);

        $this->registered = true;
    }

    #[\Override]
    public function unregister(): void
    {
        if (!$this->registered) {
            return;
        }

        restore_exception_handler();
        restore_error_handler();

        $this->registered = false;
    }

    #[\Override]
    public function handleException(Throwable $exception): void
    {
        $this->log($exception);
        ($this->outputCallback)($this->formatMessage($exception));
        ($this->terminateCallback)(1);
    }

    /** @psalm-suppress PossiblyUnusedReturnValue */
    #[\Override]
    public function handleError(int $severity, string $message, string $file, int $line): bool
    {
        $exception = new ErrorException($message, 0, $severity, $file, $line);
        $this->handleException($exception);
        return true; // @codeCoverageIgnore
    }

    #[\Override]
    public function handleShutdown(): void
    {
        if (!$this->registered) {
            return;
        }

        /** @var array{type: int, message: string, file: string, line: int}|null $error */
        $error = ($this->errorGetLastCallback)();
        if ($error === null || !in_array($error['type'], self::FATAL_ERROR_TYPES, true)) {
            return;
        }

        $this->handleException(new ErrorException(
            $error['message'],
            0,
            $error['type'],
            $error['file'],
            $error['line']
        ));
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
        $separator = strrchr($className, '\\');
        $exceptionName = $separator !== false
            ? substr($separator, 1)
            : $className;

        return sprintf(
            "Fatal error: %s\nMessage: %s\nLocation: %s:%d\n\nTrace:\n%s\n",
            $exceptionName,
            $exception->getMessage(),
            $origin->getFile(),
            $origin->getLine(),
            $exception->getTraceAsString()
        );
    }

    public function setOutputMode(BootstrapErrorHandlerMode $mode): void
    {
        $this->mode = $mode;
    }

    private function resolveOrigin(Throwable $exception): Throwable
    {
        return $exception->getPrevious() ?? $exception;
    }

    private function resolveMode(): BootstrapErrorHandlerMode
    {
        if ($this->mode !== null) {
            return $this->mode;
        }

        if ($this->isCliServer()) {
            return BootstrapErrorHandlerMode::CLI_SERVER;
        }

        if ($this->isCliSapi()) {
            return BootstrapErrorHandlerMode::CLI;
        }

        return BootstrapErrorHandlerMode::HTTP;
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
