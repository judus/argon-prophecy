<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Http\Server\Factory;

use Maduser\Argon\Middleware\Contracts\RequestHandlerFactoryInterface as MiddlewareRequestHandlerFactoryInterface;
use Maduser\Argon\Http\Server\MiddlewarePipeline;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Psr\Http\Server\RequestHandlerInterface;

interface RequestHandlerFactoryInterface extends MiddlewareRequestHandlerFactoryInterface
{
    /**
     * @throws NotFoundException
     * @throws ContainerException
     * @psalm-suppress PossiblyUnusedReturnValue
     */
    public function create(string $cacheKey = 'http_pipeline'): RequestHandlerInterface;
}
