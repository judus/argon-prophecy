<?php

namespace Maduser\Argon\Middleware;

use Maduser\Argon\Middleware\Contracts\MiddlewarePipelineCacheInterface;
use Maduser\Argon\Middleware\Resolver\ContainerMiddlewareResolver;

class MiddlewarePipelineCache implements MiddlewarePipelineCacheInterface
{

    public function get(string $key): ?MiddlewarePipeline
    {
        return null;
    }

    public function set(string $key, MiddlewarePipeline $pipeline): void
    {
        // TODO: Implement set() method.
    }
}