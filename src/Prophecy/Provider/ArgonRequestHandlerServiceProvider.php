<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Provider;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Contracts\Http\Server\Factory\RequestHandlerFactoryInterface;
use Maduser\Argon\Contracts\Support\ResultContextInterface;
use Maduser\Argon\Http\Server\Factory\RequestHandlerFactory;
use Maduser\Argon\Http\Server\MiddlewarePipeline;
use Maduser\Argon\Prophecy\Support\Tag;
use Maduser\Argon\Support\ResultContext;
use Psr\Http\Server\RequestHandlerInterface;

class ArgonRequestHandlerServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $container->set(RequestHandlerFactoryInterface::class, RequestHandlerFactory::class)
            ->tag([Tag::REQUEST_HANDLER_FACTORY]);

        $container->set(RequestHandlerInterface::class, MiddlewarePipeline::class)
            ->factory(RequestHandlerFactoryInterface::class, 'create')
            ->tag([Tag::MIDDLEWARE_PIPELINE, Tag::PSR15]);

        $container->set(ResultContextInterface::class, ResultContext::class);
    }
}
