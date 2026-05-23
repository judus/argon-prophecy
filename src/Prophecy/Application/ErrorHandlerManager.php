<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Application;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Contracts\Handler\AppHandlerInterface;
use Maduser\Argon\Contracts\Handler\CliKernelInterface;
use Maduser\Argon\Contracts\Handler\HttpKernelInterface;
use Maduser\Argon\Prophecy\ErrorHandling\BootstrapErrorHandlerMode;
use Maduser\Argon\Prophecy\Contracts\ErrorHandling\BootstrapErrorHandlerInterface;
use Maduser\Argon\Support\Contracts\ErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

/**
 * Coordinates runtime error handlers and fallback behaviour.
 *
 * @internal
 */
final class ErrorHandlerManager
{
    private ?ErrorHandlerInterface $runtimeHandler = null;

    public function __construct(
        private readonly BootstrapErrorHandlerInterface $bootstrapHandler,
        private ?LoggerInterface $logger = null
    ) {
    }

    public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function registerRuntimeHandlerIfAvailable(ArgonContainer $container): void
    {
        if ($this->runtimeHandler !== null) {
            return;
        }

        if (!$container->has(ErrorHandlerInterface::class)) {
            $this->logger?->warning('No ErrorHandler registered — falling back to BootstrapErrorHandler.');
            return;
        }

        try {
            $handler = $container->get(ErrorHandlerInterface::class);
        } catch (Throwable $exception) {
            $this->logger?->critical('Failed to resolve ErrorHandlerInterface from container.', [
                'exception' => $exception,
            ]);

            throw new RuntimeException(
                'Runtime error handler is registered but could not be resolved.',
                0,
                $exception
            );
        }

        if (!$handler instanceof ErrorHandlerInterface) {
            throw new RuntimeException(sprintf(
                'Runtime error handler binding %s must resolve to %s; got %s.',
                ErrorHandlerInterface::class,
                ErrorHandlerInterface::class,
                $handler::class
            ));
        }

        try {
            $handler->register();
            $this->runtimeHandler = $handler;
            $this->logger?->info('Runtime error handler registered.', [
                'class' => $handler::class,
            ]);
        } catch (Throwable $exception) {
            $this->logger?->critical('Runtime error handler failed during registration.', [
                'exception' => $exception,
            ]);

            throw new RuntimeException(
                'Runtime error handler is registered but failed during registration.',
                0,
                $exception
            );
        }
    }

    public function configureBootstrapModeFor(AppHandlerInterface $handler): void
    {
        if (!method_exists($this->bootstrapHandler, 'setOutputMode')) {
            return;
        }

        if ($handler instanceof CliKernelInterface) {
            $this->bootstrapHandler->setOutputMode(BootstrapErrorHandlerMode::CLI);
            return;
        }

        $this->bootstrapHandler->setOutputMode(BootstrapErrorHandlerMode::HTTP);
    }

    public function handleHttpThrowable(
        Throwable $throwable,
        ?ServerRequestInterface $request,
        ArgonContainer $container
    ): ?ResponseInterface {
        $resolvedRequest = $this->resolveRequest($request, $container);

        if ($resolvedRequest !== null && $this->runtimeHandler !== null) {
            try {
                return $this->runtimeHandler->handle($throwable, $resolvedRequest);
            } catch (Throwable $handlerFailure) {
                $this->logger?->critical('Runtime error handler failed.', [
                    'exception' => $handlerFailure,
                ]);
            }
        } elseif ($this->runtimeHandler === null) {
            $this->logger?->warning('Runtime error handler unavailable; using BootstrapErrorHandler.');
        }

        $this->bootstrapHandler->handleException($throwable);

        return null;
    }

    public function emitResponse(HttpKernelInterface $kernel, ResponseInterface $response): void
    {
        try {
            $kernel->emit($response);
            $kernel->terminate($this->determineExitCode($response));
        } catch (Throwable $emitFailure) {
            $this->logger?->critical('Failed to emit fallback response.', [
                'exception' => $emitFailure,
            ]);

            $this->bootstrapHandler->handleException($emitFailure);
        }
    }

    public function handleCliThrowable(Throwable $throwable): void
    {
        $this->bootstrapHandler->handleException($throwable);
    }

    private function resolveRequest(
        ?ServerRequestInterface $request,
        ArgonContainer $container
    ): ?ServerRequestInterface {
        if ($request instanceof ServerRequestInterface) {
            return $request;
        }

        if ($container->has(ServerRequestInterface::class)) {
            try {
                $resolved = $container->get(ServerRequestInterface::class);
                if ($resolved instanceof ServerRequestInterface) {
                    return $resolved;
                }
            } catch (Throwable $exception) {
                $this->logger?->debug('Unable to resolve ServerRequestInterface from container.', [
                    'exception' => $exception,
                ]);
            }
        }

        return null;
    }

    private function determineExitCode(ResponseInterface $response): int
    {
        return $response->getStatusCode() >= 500 ? 1 : 0;
    }
}
