<?php

declare(strict_types=1);

namespace Maduser\Argon\Http;

use Maduser\Argon\Contracts\KernelInterface;
use Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class Kernel implements KernelInterface
{
    public function __construct(
        private ServerRequestInterface $request,
        private RequestHandlerInterface $handler,
        private ExceptionHandlerInterface $exceptionHandler,
        private ?LoggerInterface $logger = null,
    ) {
        $this->exceptionHandler->register();
        $this->logger?->info('Exception handler registered', ['class' => get_class($this->exceptionHandler)]);
    }

    public function handle(): void
    {
        try {
            $this->logger?->info('Handling request', [
                'method' => $this->request->getMethod(),
                'uri' => (string) $this->request->getUri(),
            ]);

            $response = $this->handler->handle($this->request);
        } catch (Throwable $e) {
            $this->handleThrowable($e);
            return;
        }

        $this->emit($response);
    }

    private function emit(ResponseInterface $response): void
    {
        $this->logger?->info('Emitting response', [
            'status' => $response->getStatusCode(),
            'type' => $response->getHeaderLine('Content-Type'),
            'size' => $response->getBody()->getSize(),
        ]);

        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            if (strtolower((string) $name) === 'content-length') {
                $size = $response->getBody()->getSize();
                if ($size !== null) {
                    header("Content-Length: $size", false);
                }
                continue;
            }

            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }

        echo $response->getBody();
    }

    private function handleThrowable(Throwable $e): void
    {
        try {
            $response = $this->exceptionHandler->handle($e, $this->request);
            $this->emit($response);
            $this->terminate(1);
        } catch (Throwable $handlerFailure) {
            $this->logger?->critical('render() threw during exception handling', [
                'exception' => $handlerFailure,
            ]);
            $this->emitRaw($e);
        }
    }

    private function emitRaw(Throwable $e): void
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

        $this->terminate(1);
    }

    /**
     * Terminates the application.
     *
     * @param int $code Exit code.
     *
     * @api This method can be overridden in custom kernels for graceful shutdown or testing.
     */
    protected function terminate(int $code): void
    {
        exit($code);
    }
}
