<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Factory;

use Maduser\Argon\Middleware\Contracts\MiddlewareLoaderInterface;
use Maduser\Argon\Middleware\Contracts\MiddlewarePipelineCacheInterface;
use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\MiddlewareDefinition;
use Maduser\Argon\Middleware\MiddlewarePipeline;
use Maduser\Argon\Middleware\MiddlewarePipelineBuilder;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class RequestHandlerFactory
{
    public function __construct(
        private MiddlewareResolverInterface $resolver,
        private LoggerInterface $logger,
        //private MiddlewareLoaderInterface $loader,
        //private ?MiddlewarePipelineCacheInterface $cache = null
    ) {
    }

//    public function create(string $cacheKey = 'http_pipeline'): RequestHandlerInterface
//    {
//        dd('WTF?!');
//        if ($this->cache && ($cached = $this->cache->get($cacheKey))) {
//            return $cached;
//        }
//
//        $builder = new MiddlewarePipelineBuilder($this->resolver, $this->logger);
//
//        $groups = $this->loader->loadGrouped();
//        foreach ($groups as $groupName => $definitions) {
//            foreach ($definitions as $definition) {
//                $builder->registerAlias($definition->class, $definition->class);
//            }
//            if ($groupName === MiddlewareDefinition::DEFAULT_GROUP) {
//                foreach ($definitions as $definition) {
//                    $builder->addMiddleware($definition->class, $definition->priority);
//                }
//            } else {
//                $builder->registerGroup($groupName, array_map(fn($d) => $d->class, $definitions));
//                $builder->addGroup($groupName);
//            }
//        }
//
//        $pipeline = $builder->build();
//
//        $this->cache?->set($cacheKey, $pipeline);
//
//        return $pipeline;
//    }

    public function createFromStack(array $middleware): MiddlewarePipeline
    {
        foreach ($middleware as $item) {
            if (!is_string($item) && !$item instanceof \Psr\Http\Server\MiddlewareInterface) {
                throw new \InvalidArgumentException(sprintf(
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
}
