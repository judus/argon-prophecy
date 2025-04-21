<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Contracts;

use Maduser\Argon\Middleware\MiddlewarePipeline;

interface MiddlewarePipelineCacheInterface
{
    public function get(string $key): ?MiddlewarePipeline;
    public function set(string $key, MiddlewarePipeline $pipeline): void;
}
