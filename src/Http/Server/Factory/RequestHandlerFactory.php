<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Factory;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Http\Server\MiddlewarePipeline;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;

final readonly class RequestHandlerFactory
{
    public function __construct(
        private ArgonContainer $container,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function create(): MiddlewarePipeline
    {
        $pipeline = new MiddlewarePipeline($this->logger);

        foreach ($this->container->getTagged('middleware.http') as $middleware) {
            if (!$middleware instanceof MiddlewareInterface) {
                throw new \RuntimeException("Tagged middleware must implement MiddlewareInterface");
            }

            $pipeline->pipe($middleware);
        }

        return $pipeline;
    }
}
