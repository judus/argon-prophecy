<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Factory;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Contracts\Http\Server\Factory\RequestHandlerFactoryInterface;
use Maduser\Argon\Http\Server\MiddlewarePipeline;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

final readonly class RequestHandlerFactory implements RequestHandlerFactoryInterface
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

        $entries = [];

        /**
         * @var array<string, array{priority: int, id: string}> $middlewares
         */
        $middlewares = $this->container->getTaggedMeta('middleware.http');

        foreach ($middlewares as $id => $meta) {
            $entries[] = [
                'priority' => $meta['priority'] ?? 0,
                'id' => $id,
            ];
        }

        usort($entries, fn(array $a, array $b): int => $b['priority'] <=> $a['priority']);

        foreach ($entries as $entry) {
            $middleware = $this->container->get($entry['id']);

            if (!$middleware instanceof MiddlewareInterface) {
                throw new RuntimeException("Service '{$entry['id']}' must implement MiddlewareInterface.");
            }

            $pipeline->pipe($middleware);
        }

        return $pipeline;
    }
}
