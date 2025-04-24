<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Contracts;

use Maduser\Argon\Contracts\MiddlewareStackInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface PipelineManagerInterface
{
    public function register(MiddlewareStackInterface $stack): void;

    public function get(MiddlewareStackInterface|string $keyOrStack): RequestHandlerInterface;
}
