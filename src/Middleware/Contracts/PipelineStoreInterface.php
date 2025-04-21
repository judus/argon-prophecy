<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Contracts;

use Maduser\Argon\Middleware\MiddlewareStack;
use Psr\Http\Server\RequestHandlerInterface;

interface PipelineStoreInterface
{
    /**
     * @param MiddlewareStack|string $keyOrStack
     */
    public function get(MiddlewareStack|string $keyOrStack): RequestHandlerInterface;

    public function register(MiddlewareStack $stack): self;
}
