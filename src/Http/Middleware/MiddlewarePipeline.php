<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Middleware;

use Maduser\Argon\Http\Factory\ResponseFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MiddlewarePipeline implements RequestHandlerInterface
{
    /** @var MiddlewareInterface[] */
    private array $middleware = [];

    private RequestHandlerInterface $finalHandler;

    public function __construct(?RequestHandlerInterface $finalHandler = null)
    {
        $this->finalHandler = $finalHandler ?? new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return ResponseFactory::text('Nobody cared about your request', 200);
            }
        };
    }

    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;
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

        return new class (
            $this->middleware[$index],
            $this->createHandler($index + 1)
        ) implements RequestHandlerInterface {
            public function __construct(
                private MiddlewareInterface $middleware,
                private RequestHandlerInterface $next
            ) {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->middleware->process($request, $this->next);
            }
        };
    }
}
