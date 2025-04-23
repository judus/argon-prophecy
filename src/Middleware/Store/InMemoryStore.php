<?php

namespace Maduser\Argon\Middleware\Store;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Contracts\MiddlewareStackInterface;
use Maduser\Argon\Middleware\Contracts\PipelineStoreInterface;
use Maduser\Argon\Middleware\MiddlewarePipeline;
use Maduser\Argon\Middleware\MiddlewareStack;
use Maduser\Argon\Middleware\MiddlewareVerbosity;
use Maduser\Argon\Middleware\Resolver\ContainerMiddlewareResolver;
use Monolog\Logger;
use Psr\Http\Server\RequestHandlerInterface;

class InMemoryStore implements PipelineStoreInterface
{
    public function __construct()
    {
    }

    public function get(MiddlewareStackInterface|string $keyOrStack): RequestHandlerInterface
    {
        return new MiddlewarePipeline(
            middleware: $keyOrStack->toArray(),
            resolver: new ContainerMiddlewareResolver(
                container: new ArgonContainer()
            ),
            logger: new Logger(name: 'logger'),
            finalHandler: null,
            verbosity: MiddlewareVerbosity::NORMAL
        );
    }

    public function register(MiddlewareStackInterface $stack): PipelineStoreInterface
    {
        return $this;
    }
}