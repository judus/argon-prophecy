<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Http\Server\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

interface DispatcherInterface extends MiddlewareInterface
{
    public function dispatch(ServerRequestInterface $request): void;
}
