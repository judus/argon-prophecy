<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Exception;

use ErrorException;
use Psr\Log\LoggerInterface;
use Throwable;

final class BootstrapExceptionHandler
{
    private ?LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function register(): void
    {
        set_exception_handler(function (Throwable $e): void {
            $this->log($e);
            $this->output($e);
            exit(1);
        });

        set_error_handler(function (
            int $severity,
            string $message,
            string $file,
            int $line
        ): bool {
            $e = new ErrorException($message, 0, $severity, $file, $line);
            $this->log($e);
            $this->output($e);
            exit(1);
        });
    }

    private function log(Throwable $e): void
    {
        try {
            $this->logger?->error('Unhandled bootstrap exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        } catch (Throwable) {
            // Logging failed. Ignore.
        }
    }

    private function output(Throwable $e): void
    {
        try {
            $message = sprintf(
                "Fatal error: %s in %s:%d\n",
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            );

            if (PHP_SAPI === 'cli') {
                fwrite(STDERR, $message);
            } else {
                http_response_code(500);
                echo '<pre>' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
            }
        } catch (Throwable) {
            // We're doomed anyway.
        }
    }
}
