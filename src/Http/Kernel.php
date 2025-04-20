<?php

declare(strict_types=1);

namespace Maduser\Argon\Http;

use ErrorException;
use Maduser\Argon\Contracts\KernelInterface;
use Maduser\Argon\Contracts\Http\Exception\ExceptionHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class Kernel implements KernelInterface
{
    private bool $handlersRegistered = false;

    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly RequestHandlerInterface $handler,
        private readonly LoggerInterface $logger,
        private readonly ExceptionHandlerInterface $exceptionHandler,
    ) {
    }

    public function handle(): void
    {
        $this->logger->info('Handling request', [
            'method' => $this->request->getMethod(),
            'uri' => (string) $this->request->getUri(),
        ]);

        $this->setupErrorHandling();

        try {
            $response = $this->handler->handle($this->request);
        } catch (Throwable $throwable) {
            $this->handleThrowable($throwable);
        }

        $this->emit($response);
    }

    private function emit(ResponseInterface $response): void
    {
        $this->logger->info('Emitting response', [
            'status' => $response->getStatusCode(),
            'type' => $response->getHeaderLine('Content-Type'),
            'length' => $response->getBody()->getSize(),
        ]);

        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }

        echo $response->getBody();
    }

    private function setupErrorHandling(): void
    {
        if ($this->handlersRegistered) {
            return;
        }

        $this->handlersRegistered = true;

        set_error_handler(function (
            int $severity,
            string $message,
            string $file,
            int $line
        ): bool {
            try {
                $throwable = new ErrorException($message, 0, $severity, $file, $line);
                $this->exceptionHandler->report($throwable);
            } catch (Throwable $loggingFailure) {
                $this->logger->critical('Error reporting failed in set_error_handler', [
                    'error' => $message,
                    'exception' => $loggingFailure,
                ]);
            }

            return true;
        });

        set_exception_handler(fn(Throwable $e) => $this->handleThrowable($e));

        register_shutdown_function(function (): void {
            $error = error_get_last();

            if ($this->isFatalError($error)) {
                $message = $error['message'] ?? 'Unknown fatal error';
                $type = $error['type'] ?? E_ERROR;
                $file = $error['file'] ?? 'unknown';
                $line = $error['line'] ?? 0;

                $throwable = new ErrorException($message, 0, $type, $file, $line);
                $this->handleThrowable($throwable);
            }
        });
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

    private function handleThrowable(Throwable $e): void
    {
        try {
            $response = $this->exceptionHandler->handle($e, $this->request);
            $this->emit($response);
            exit(1);
        } catch (Throwable $handlerFailure) {
            $this->logger->critical('render() threw during exception handling', [
                'exception' => $handlerFailure,
            ]);
            $this->sendBareResponse($e);
        }
    }

    private function sendBareResponse(Throwable $e): void
    {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=UTF-8');
        }

        echo sprintf(
            "Fatal error: %s in %s:%d\n",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );

        exit(1);
    }
}
