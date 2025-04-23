<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Contracts;

use Maduser\Argon\Contracts\MiddlewareStackInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface PipelineStoreInterface
{
    /**
     * @param MiddlewareStackInterface|string $keyOrStack
     */
    public function get(MiddlewareStackInterface|string $keyOrStack): RequestHandlerInterface;

    public function register(MiddlewareStackInterface $stack): self;
}
