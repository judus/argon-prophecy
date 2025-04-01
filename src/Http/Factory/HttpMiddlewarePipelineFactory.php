<?php

namespace Maduser\Argon\Http\Factory;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Http\Middleware\MiddlewarePipeline;
use Psr\Http\Server\MiddlewareInterface;

final readonly class HttpMiddlewarePipelineFactory
{
    public function __construct(
        private ArgonContainer $container
    ) {}

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function create(): MiddlewarePipeline
    {
        $pipeline = new MiddlewarePipeline();

        foreach ($this->container->getTagged('middleware.http') as $middleware) {
            if (!$middleware instanceof MiddlewareInterface) {
                throw new \RuntimeException("Tagged middleware must implement MiddlewareInterface");
            }

            $pipeline->pipe($middleware);
        }

        return $pipeline;
    }
}