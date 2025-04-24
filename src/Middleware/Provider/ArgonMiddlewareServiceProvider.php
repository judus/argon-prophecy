<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Provider;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Contracts\Http\Server\Factory\RequestHandlerFactoryInterface;
use Maduser\Argon\Middleware\Factory\RequestHandlerFactory;
use Maduser\Argon\Middleware\Contracts\MiddlewareLoaderInterface;
use Maduser\Argon\Middleware\Contracts\MiddlewarePipelineCacheInterface;
use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Contracts\PipelineStoreInterface;
use Maduser\Argon\Middleware\Loader\TaggedMiddlewareLoader;
use Maduser\Argon\Middleware\MiddlewarePipeline;
use Maduser\Argon\Middleware\MiddlewarePipelineCache;
use Maduser\Argon\Middleware\PipelineManager;
use Maduser\Argon\Middleware\Resolver\ContainerMiddlewareResolver;
use Maduser\Argon\Middleware\Store\ContainerStore;
use Maduser\Argon\Prophecy\Support\Tag;
use Psr\Http\Server\RequestHandlerInterface;

class ArgonMiddlewareServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $container->set(PipelineManager::class, args: [
            'store' => ContainerStore::class
        ]);

        $container->set(PipelineStoreInterface::class, ContainerStore::class)
            ->tag(['middleware.store']);

        $container->set(MiddlewareLoaderInterface::class, TaggedMiddlewareLoader::class)
            ->tag(['middleware.loader']);

        $container->set(MiddlewarePipelineCacheInterface::class, MiddlewarePipelineCache::class)
            ->tag(['middleware.cache']);

        $container->set(MiddlewareResolverInterface::class, ContainerMiddlewareResolver::class)
            ->tag(['middleware.resolver']);

        /**
         * Override the default middleware pipeline
         */
        $container->set(RequestHandlerFactory::class);

        $container->set(RequestHandlerFactoryInterface::class, RequestHandlerFactory::class)
            ->tag([Tag::REQUEST_HANDLER_FACTORY]);

        $container->set(RequestHandlerInterface::class, MiddlewarePipeline::class)
            ->factory(RequestHandlerFactoryInterface::class, 'createFromRouteContext')
            ->tag([Tag::MIDDLEWARE_HTTP, Tag::PSR15]);
    }
}
