<?php

declare(strict_types=1);

namespace Maduser\Argon\Console\Factory;

use Maduser\Argon\Console\Middleware\CliMiddlewarePipeline;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;

final class CliMiddlewarePipelineFactory
{
    public function __construct(private ArgonContainer $container)
    {
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function create(): CliMiddlewarePipeline
    {
        $pipeline = new CliMiddlewarePipeline();

        foreach ($this->container->getTagged('middleware.cli') as $middleware) {
            $pipeline->pipe($middleware);
        }

        return $pipeline;
    }
}
