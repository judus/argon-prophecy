<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Factory;

use InvalidArgumentException;
use Maduser\Argon\Middleware\Contracts\MiddlewareLoaderInterface;
use Maduser\Argon\Middleware\Contracts\MiddlewarePipelineCacheInterface;
use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Contracts\PipelineManagerInterface;
use Maduser\Argon\Middleware\MiddlewareDefinition;
use Maduser\Argon\Middleware\MiddlewarePipeline;
use Maduser\Argon\Middleware\MiddlewarePipelineBuilder;
use Maduser\Argon\Routing\Contracts\RequestHandlerResolverInterface;
use Maduser\Argon\Routing\Contracts\RouteContextInterface;
use Maduser\Argon\Routing\RequestHandlerResolver;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class RequestHandlerFactory
{
    public function __construct(
        private MiddlewareResolverInterface $resolver,
        private RequestHandlerResolverInterface $requestHandlerResolver,
        private RouteContextInterface $context,
        private LoggerInterface $logger,
        private MiddlewareLoaderInterface $loader,
        private ?PipelineManagerInterface $pipelines = null
    ) {
    }

    public function create(string $cacheKey = 'http_pipeline'): RequestHandlerInterface
    {
        $cached =  $this->pipelines?->get($cacheKey);

        if ($cached instanceof RequestHandlerInterface) {
            return $cached;
        }

        $builder = new MiddlewarePipelineBuilder($this->resolver, $this->logger);

        $groups = $this->loader->loadGrouped();
        foreach ($groups as $groupName => $definitions) {
            foreach ($definitions as $definition) {
                $builder->registerAlias($definition->class, $definition->class);
            }
            if ($groupName === MiddlewareDefinition::DEFAULT_GROUP) {
                foreach ($definitions as $definition) {
                    $builder->addMiddleware($definition->class, $definition->priority);
                }
            } else {
                $builder->registerGroup($groupName, array_map(fn($d) => $d->class, $definitions));
                $builder->addGroup($groupName);
            }
        }

        return $builder->build();
    }

    /**
     * @param list<class-string<MiddlewareInterface>|MiddlewareInterface> $middleware
     */
    public function createFromStack(array $middleware): MiddlewarePipeline
    {
        foreach ($middleware as $item) {
            if (!is_string($item) && !$item instanceof MiddlewareInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Middleware must be class-string or instance of MiddlewareInterface. Got: %s',
                    get_debug_type($item)
                ));
            }
        }

        return new MiddlewarePipeline(
            middleware: $middleware,
            resolver: $this->resolver,
            logger: $this->logger
        );
    }

    public function createFromRouteContext(?ServerRequestInterface $request = null): RequestHandlerInterface
    {
        return $this->requestHandlerResolver->resolve($request);
    }
}
