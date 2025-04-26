<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Provider;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Contracts\KernelInterface;
use Maduser\Argon\Http\Kernel;
use Maduser\Argon\Logging\LoggerServiceProvider;
use Maduser\Argon\Prophecy\Support\Tag;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ArgonHttpFoundation extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function register(ArgonContainer $container): void
    {
        if (!$container->has(LoggerInterface::class)) {
            $container->set(LoggerInterface::class, NullLogger::class);
        }

        /** Exception Handler */
        $container->register(ArgonExceptionServiceProvider::class);

        /** Kernel */
        $container->set(KernelInterface::class, Kernel::class, [
            'logger' => LoggerInterface::class,
        ])->tag([Tag::KERNEL]);

        /** PSR-17/7: HTTP Messages */
        $container->register(ArgonMessageServiceProvider::class);

        /** PSR-15: RequestHandler/MiddlewarePipeline */
        $container->register(ArgonRequestHandlerServiceProvider::class);

        /** Middlewares */
        $container->register(ArgonMiddlewareServiceProvider::class);
    }
}
