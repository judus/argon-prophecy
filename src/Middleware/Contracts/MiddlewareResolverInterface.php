<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Contracts;

use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareResolverInterface
{
    public function resolve(string $class): MiddlewareInterface;
}
