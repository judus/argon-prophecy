<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\ServiceProviders;

use Maduser\Argon\Console\ArgonConsoleAdapter;
use Maduser\Argon\Console\ArgonConsoleInput;
use Maduser\Argon\Console\ArgonConsoleOutput;
use Maduser\Argon\Console\Contracts\ConsoleInputInterface;
use Maduser\Argon\Console\Contracts\ConsoleInterface;
use Maduser\Argon\Console\Contracts\ConsoleOutputInterface;
use Maduser\Argon\Console\Factory\CliMiddlewarePipelineFactory;
use Maduser\Argon\Console\Middleware\CliMiddlewarePipeline;
use Maduser\Argon\Console\Middleware\CommandDispatcherMiddleware;
use Maduser\Argon\Console\SymfonyConsoleAdapter;
use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Kernel\CliKernel;

class ArgonCliFoundation extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $container->singleton(CommandDispatcherMiddleware::class)
            ->tag(['middleware.cli']);

        $container->singleton(CliMiddlewarePipeline::class)
            ->useFactory(CliMiddlewarePipelineFactory::class, 'create')
            ->tag(['cli.pipeline']);

        $container->singleton(ConsoleOutputInterface::class, ArgonConsoleOutput::class);
        $container->singleton(ConsoleInputInterface::class, ArgonConsoleInput::class);
        $container->getArgumentMap()->set(ArgonConsoleInput::class, [
            'argv' => $_SERVER['argv'] ?? [],
        ]);

        $container->singleton(ConsoleInterface::class, ArgonConsoleAdapter::class)
            ->tag(['console.adapter']);

        $container->bind(CliKernel::class)
            ->tag('kernel.cli');
    }
}
