<?php

declare(strict_types=1);

namespace Maduser\Argon\Console\Factory;

use Maduser\Argon\Console\ConsolePipeline;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;

final class ConsolePipelineFactory
{
    public function __construct(private ArgonContainer $container)
    {
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function create(): ConsolePipeline
    {
        $pipeline = new ConsolePipeline();

        foreach ($this->container->getTagged('middleware.cli') as $middleware) {
            $pipeline->pipe($middleware);
        }

        return $pipeline;
    }
}
