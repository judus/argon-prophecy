<?php

declare(strict_types=1);

namespace Maduser\Argon\Http;

use Maduser\Argon\Contracts\KernelInterface;
use Maduser\Argon\Contracts\ErrorHandling\Http\ErrorHandlerInterface;
use Maduser\Argon\Contracts\Http\ResponseEmitterInterface;
use Maduser\Argon\Http\Message\Response;
use Maduser\Argon\Http\Message\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Kernel handles the HTTP lifecycle and acts as a catastrophic failure safeguard.
 *
 * It *attempts* to use the provided ExceptionHandlerInterface and ResponseEmitterInterface
 * implementations. If these services are misconfigured, unavailable, or fail internally,
 * Kernel falls back to internal hardcoded mechanisms to guarantee a basic 500 Internal Server Error.
 */
final readonly class Kernel implements KernelInterface
{
    public function __construct(
        private ErrorHandlerInterface $exceptionHandler,
        private ServerRequestInterface $request,
        private RequestHandlerInterface $handler,
        private ResponseEmitterInterface $emitter,
        private ?LoggerInterface $logger = null,
        private bool $debug = false,
        private bool $shouldExit = true,
    ) {
        $this->safeRegisterExceptionHandler();
        $this->registerShutdownHandler();
    }

    /**
     * Ensures the ExceptionHandler is registered.
     *
     * In a typical application, the ExceptionHandler should be registered during the ServiceProvider boot phase.
     *
     * This method provides an additional safeguard to ensure that even if the ExceptionHandler was not registered
     * correctly due to customizations or misconfiguration, the Kernel can recover gracefully and guarantee
     * a valid PSR-7 response.
     *
     * If registration fails, a fallback internal server error response will be emitted immediately.
     */
    private function safeRegisterExceptionHandler(): void
    {
        try {
            $this->exceptionHandler->register();
        } catch (Throwable $e) {
            $this->log('critical', 'Failed to register exception handler', [
                'exception' => $e->getMessage(),
                'trace' => $this->debug ? $e->getTraceAsString() : null,
            ]);

            $fallbackResponse = $this->buildFallbackResponse($e);
            $this->fallbackEmit($fallbackResponse);
            $this->terminate(1, $this->shouldExit);
        }
    }

    /**
     * Registers a shutdown function to catch fatal PHP errors.
     */
    private function registerShutdownHandler(): void
    {
        /** @noinspection DuplicatedCode */
        register_shutdown_function(function (): void {
            $error = error_get_last();

            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                $this->log('emergency', 'Fatal shutdown error detected', $error);

                $fallbackResponse = $this->buildFallbackResponse($error);
                $this->fallbackEmit($fallbackResponse);
                $this->terminate(1, $this->shouldExit);
            }
        });
    }

    /**
     * Attempts to log messages without risking fatal errors.
     *
     * @param string $level
     * @param string $message
     * @param array<string, mixed> $context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger === null) {
            return;
        }

        try {
            $this->logger->{$level}($message, $context);
        } catch (Throwable) {
            // This is a safeguard to ensure that logging errors do not interfere with the HTTP lifecycle.
            // Don't see logs => Logger is misconfigured.
        }
    }

    /**
     * Handles the full HTTP lifecycle.
     *
     * @param ServerRequestInterface|null $request Optional PSR-7 request (defaults to constructor request)
     */
    public function handle(?ServerRequestInterface $request = null): void
    {
        $response = $this->process($request);
        $this->emit($response);
        $this->terminate($this->getExitCode($response), $this->shouldExit);
    }

    /**
     * Captures the response from the middleware pipeline.
     *
     * @param ServerRequestInterface|null $request Optional PSR-7 request (defaults to constructor request)
     * @return ResponseInterface
     */
    public function process(?ServerRequestInterface $request = null): ResponseInterface
    {
        try {
            $request ??= $this->request;

            $this->log('info', 'Handling request', [
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
            ]);

            return $this->handler->handle($request);
        } catch (Throwable $e) {
            return $this->handleThrowable($e);
        }
    }

    /**
     * Handles a caught Throwable.
     *
     * @param Throwable $exception
     * @return ResponseInterface
     */
    private function handleThrowable(Throwable $exception): ResponseInterface
    {
        try {
            return $this->exceptionHandler->handle($exception, $this->request);
        } catch (Throwable $handlerFailure) {
            $this->log('critical', 'ExceptionHandler failed', [
                'original_exception' => get_class($exception),
                'handler_exception' => get_class($handlerFailure),
                'trace' => $this->debug ? $handlerFailure->getTraceAsString() : null,
            ]);

            return $this->buildFallbackResponse($exception);
        }
    }

    /**
     * Builds a fallback response for normal exceptions and shutdown errors.
     *
     * @param Throwable|array{type: int, message: string, file: string, line: int} $error
     * @return ResponseInterface
     */
    private function buildFallbackResponse(Throwable|array $error): ResponseInterface
    {
        $body = $this->debug
            ? (is_array($error)
                ? sprintf("Fatal shutdown error: %s in %s:%d\n", $error['message'], $error['file'], $error['line'])
                : sprintf("Fatal error: %s in %s:%d\n", $error->getMessage(), $error->getFile(), $error->getLine()))
            : 'Internal Server Error';

        return new Response(
            body: new Stream($body),
            status: 500,
            headers: ['Content-Type' => 'text/plain; charset=UTF-8'],
            reasonPhrase: 'Internal Server Error'
        );
    }

    /**
     * Emits the response.
     *
     * @param ResponseInterface $response
     */
    public function emit(ResponseInterface $response): void
    {
        $this->log('info', 'Emitting response', [
            'status' => $response->getStatusCode(),
        ]);

        try {
            $this->emitter->emit($response);
        } catch (Throwable $e) {
            $this->log('critical', 'ResponseEmitterInterface failed', ['exception' => $e->getMessage()]);

            $fallbackResponse = $this->buildFallbackResponse($e);
            $this->fallbackEmit($fallbackResponse);
        }
    }

    /**
     * Emits a response manually if emitter fails.
     *
     * @param ResponseInterface $response
     */
    private function fallbackEmit(ResponseInterface $response): void
    {
        if (headers_sent()) {
            $this->log('critical', 'Cannot send headers; headers already sent.');
            $this->emitBody($response);
            return;
        }

        $this->log('error', 'Fallback emitting response', [
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

        $this->emitBody($response);
    }

    /**
     * Emits the response body manually.
     *
     * @param ResponseInterface $response
     */
    private function emitBody(ResponseInterface $response): void
    {
        $body = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        $chunkSize = 8192;
        $size = $body->getSize();
        if ($size !== null && $size < $chunkSize) {
            $chunkSize = $size;
        }

        while (!$body->eof()) {
            echo $body->read($chunkSize);
        }
    }

    /**
     * Calculate exit code based on HTTP response.
     *
     * @param ResponseInterface $response
     * @return int
     */
    private function getExitCode(ResponseInterface $response): int
    {
        return $response->getStatusCode() >= 500 ? 1 : 0;
    }

    /**
     * Terminates the request lifecycle with a proper exit code.
     *
     * @param int $code
     * @param bool $shouldExit
     */
    public function terminate(int $code, bool $shouldExit = true): void
    {
        if (!$shouldExit) {
            $this->log('info', 'Bypassing terminate with code ' . $code);
            return;
        }

        // Safe hard exit after emitting response
        exit($code); // @codeCoverageIgnore
    }
}
