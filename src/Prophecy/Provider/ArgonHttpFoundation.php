<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Provider;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Contracts\KernelInterface;
use Maduser\Argon\Http\Kernel;
use Maduser\Argon\Prophecy\Support\Tag;

final class ArgonHttpFoundation extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function register(ArgonContainer $container): void
    {
        /** Kernel */
        $container->set(KernelInterface::class, Kernel::class)->tag([Tag::KERNEL]);

        /** Exception Handler */
        $container->register(ArgonExceptionServiceProvider::class);

        /** PSR-17/7: HTTP Messages */
        $container->register(ArgonMessageServiceProvider::class);

        /** PSR-15: RequestHandler/MiddlewarePipeline */
        $container->register(ArgonRequestHandlerServiceProvider::class);

        /** Middlewares */
        $container->register(ArgonMiddlewareServiceProvider::class);
    }
}
