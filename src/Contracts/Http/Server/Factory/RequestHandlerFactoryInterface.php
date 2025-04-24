<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Http\Server\Factory;

use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Http\Server\MiddlewarePipeline;

interface RequestHandlerFactoryInterface
{
    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function create(): MiddlewarePipeline;
}
