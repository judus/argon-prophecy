<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware;

use Maduser\Argon\Contracts\MiddlewareStackInterface;
use Maduser\Argon\Middleware\Contracts\PipelineManagerInterface;
use Maduser\Argon\Middleware\Contracts\PipelineStoreInterface;
use Maduser\Argon\Middleware\Store\InMemoryStore;
use Psr\Http\Server\RequestHandlerInterface;

class PipelineManager implements PipelineManagerInterface
{
    private PipelineStoreInterface $store;

    public function __construct(
        ?PipelineStoreInterface $store = null
    ) {
        $this->store = $store ?? new InMemoryStore();
    }

    public function register(MiddlewareStackInterface $stack): void
    {
        $this->store->register($stack);
    }

    public function get(MiddlewareStackInterface|string $keyOrStack): RequestHandlerInterface
    {
        return $this->store->get($keyOrStack);
    }
}
