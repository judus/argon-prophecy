<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Store;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Middleware\Contracts\PipelineStoreInterface;
use Maduser\Argon\Middleware\Factory\RequestHandlerFactory;
use Maduser\Argon\Middleware\MiddlewarePipeline;
use Maduser\Argon\Middleware\MiddlewareStack;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final readonly class ContainerStore implements PipelineStoreInterface
{
    public function __construct(
        private ArgonContainer $container
    ) {
    }

    /**
     * @param string|MiddlewareStack $keyOrStack
     * @return RequestHandlerInterface
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function get(string|MiddlewareStack $keyOrStack): RequestHandlerInterface
    {
        $pipelineId = is_string($keyOrStack)
            ? $keyOrStack
            : $keyOrStack->getId();
        //dump(['ContainerStore::get()' => $pipelineId]);
        if (!$this->container->has($pipelineId)) {
            //dump('[ContainerStore::get()] Registering new pipeline');
            if ($keyOrStack instanceof MiddlewareStack) {
                //dump(['Stack: ' => $keyOrStack->toArray()]);
                $this->register($keyOrStack);
            }
        }

        $pipeline = $this->getRequestHandler($pipelineId);

        //dump(['getRequestHandler($pipelineId)' => $pipeline]);

        return $pipeline;
    }

    /**
     * @throws ContainerException
     */
    public function register(MiddlewareStack $stack): self
    {
        $pipelineId = $stack->getId();

        if (!$this->container->has($pipelineId)) {
            $this->container->set(
                $pipelineId,
                MiddlewarePipeline::class,
                args: ['middleware' => $stack->toArray()]
            )->factory(RequestHandlerFactory::class, 'createFromStack');
        }

        //dump(['register pipeline', [$pipelineId, $this->container->get($pipelineId)]]);

        return $this;
    }

    /**
     * @param string $pipelineId
     * @return RequestHandlerInterface
     *
     * @throws ContainerException
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function getRequestHandler(string $pipelineId): RequestHandlerInterface
    {
        $handler = $this->container->get($pipelineId);

        if (!$handler instanceof RequestHandlerInterface) {
            throw new RuntimeException(
                sprintf(
                    'Container service [%s] is not a RequestHandlerInterface. Got [%s].',
                    $pipelineId,
                    $handler::class
                )
            );
        }

        return $handler;
    }
}
