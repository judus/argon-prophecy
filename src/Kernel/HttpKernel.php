<?php

declare(strict_types=1);

namespace Maduser\Argon\Kernel;

use ErrorException;
use Maduser\Argon\Kernel\Contracts\KernelInterface;
use Maduser\Argon\Kernel\Exception\ExceptionHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final class HttpKernel extends AbstractKernel
{
    public function __construct(
        private ServerRequestInterface $request,
        private RequestHandlerInterface $handler,
        private ExceptionHandlerInterface $exceptionHandler,
    ) {
    }

    public function setup(): void
    {
        $this->setupErrorHandling();
    }

    public function handle(): void
    {
        $this->setup();

        try {
            $response = $this->handler->handle($this->request);
        } catch (Throwable $throwable) {
            $this->exceptionHandler->report($throwable);
            $response = $this->exceptionHandler->render($throwable);
        }

        $this->emit($response);
    }

    private function emit(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("{$name}: {$value}", false);
            }
        }

        echo $response->getBody();
    }

    public function setupErrorHandling(): void
    {
        set_exception_handler(function (Throwable $throwable): void {
            $this->exceptionHandler->report($throwable);
            $response = $this->exceptionHandler->render($throwable);
            $this->emit($response);
        });

        register_shutdown_function(function (): void {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                $throwable = new ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line']
                );
                $this->exceptionHandler->report($throwable);
                $response = $this->exceptionHandler->render($throwable);
                $this->emit($response);
            }
        });
    }
}
