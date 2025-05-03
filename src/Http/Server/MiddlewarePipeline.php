<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server;

use Maduser\Argon\Http\Server\Exception\EmptyMiddlewareChainException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class MiddlewarePipeline implements RequestHandlerInterface
{
    /** @var list<MiddlewareInterface> */
    private array $middleware = [];

    private RequestHandlerInterface $finalHandler;

    public function __construct(
        private readonly ?LoggerInterface $logger = null,
        ?RequestHandlerInterface $finalHandler = null,
    ) {
        $this->logger?->info('Creating middleware pipeline');

        $this->finalHandler = $finalHandler ?? $this->createDefaultFinalHandler();
    }

    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;

        $this->logger?->info('Middleware registered', ['class' => get_class($middleware)]);

        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createHandler(0)->handle($request);
    }

    private function createHandler(int $index): RequestHandlerInterface
    {
        if (!isset($this->middleware[$index])) {
            return $this->finalHandler;
        }

        $middleware = $this->middleware[$index];
        $this->logger?->info('Executing middleware', ['middleware' => $middleware]);

        return new class (
            $middleware,
            $this->createHandler($index + 1)
        ) implements RequestHandlerInterface {
            public function __construct(
                private readonly MiddlewareInterface $middleware,
                private readonly RequestHandlerInterface $next,
            ) {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->middleware->process($request, $this->next);
            }
        };
    }

    private function createDefaultFinalHandler(): RequestHandlerInterface
    {
        return new class ($this->logger) implements RequestHandlerInterface {
            public function __construct(
                private readonly ?LoggerInterface $logger = null,
            ) {
                $this->logger?->info('Creating final middleware');
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->logger?->info('Middleware chain completed but no response was set.');
                throw new EmptyMiddlewareChainException();
            }
        };
    }
}
