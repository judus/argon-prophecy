<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Provider;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Contracts\ParameterStoreInterface;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Contracts\ErrorHandling\Http\ErrorHandlerInterface;
use Maduser\Argon\Contracts\Http\ResponseEmitterInterface;
use Maduser\Argon\Contracts\KernelInterface;
use Maduser\Argon\Http\Kernel;
use Maduser\Argon\Http\ResponseEmitter;
use Maduser\Argon\Prophecy\Support\Tag;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @psalm-suppress UnusedClass
 */
final class ArgonHttpFoundation extends AbstractServiceProvider
{
    public function configureParameters(ArgonContainer $container): ParameterStoreInterface
    {
        $parameters = $container->getParameters();

        if (!$parameters->has('kernel.debug')) {
            $debug = isset($_ENV['APP_DEBUG']) && strtolower((string) $_ENV['APP_DEBUG']) === 'true';
            $parameters->set('kernel.debug', $debug);
        }

        if (!$parameters->has('kernel.shouldExit')) {
            $env = strtolower(($_ENV['APP_ENV'] ?? 'production'));
            $shouldExit = $env !== 'testing';
            $parameters->set('kernel.shouldExit', $shouldExit);
        }

        return $parameters;
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function register(ArgonContainer $container): void
    {
        $parameters = $this->configureParameters($container);

        /** Exception Handler */
        $container->register(ArgonErrorHandlerServiceProvider::class);

        /** Logging */
        if (!$container->has(LoggerInterface::class)) {
            $container->set(LoggerInterface::class, NullLogger::class);
        }

        /** Kernel */
        $container->set(ResponseEmitterInterface::class, ResponseEmitter::class);

        $container->set(KernelInterface::class, Kernel::class, [
            'logger' => LoggerInterface::class,
            'debug' => $parameters->get('debug', false),
            'shouldExit' => $parameters->get('kernel.shouldExit', true),
        ])->tag([Tag::KERNEL]);

        /** PSR-17/7: HTTP Messages */
        $container->register(ArgonMessageServiceProvider::class);

        /** PSR-15: RequestHandler/MiddlewarePipeline */
        $container->register(ArgonRequestHandlerServiceProvider::class);

        /** Middlewares */
        $container->register(ArgonMiddlewareServiceProvider::class);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function boot(ArgonContainer $container): void
    {
        $container->get(ErrorHandlerInterface::class)->register();
    }
}
