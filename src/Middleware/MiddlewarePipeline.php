<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware;

use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class MiddlewarePipeline implements RequestHandlerInterface
{
    private MiddlewareDispatcher $dispatcher;

    /**
     * @param list<class-string<MiddlewareInterface>|MiddlewareInterface> $middleware
     */
    public function __construct(
        array $middleware,
        MiddlewareResolverInterface $resolver,
        LoggerInterface $logger,
        ?RequestHandlerInterface $finalHandler = null,
        int $verbosity = MiddlewareVerbosity::NORMAL
    ) {
        $this->dispatcher = new MiddlewareDispatcher(
            middleware: $middleware,
            resolver: $resolver,
            finalHandler: $finalHandler,
            logger: $logger,
            verbosity: $verbosity
        );
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->dispatcher->handle($request);
    }
}
