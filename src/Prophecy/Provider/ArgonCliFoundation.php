<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Provider;

use Maduser\Argon\Console\ArgonConsole;
use Maduser\Argon\Console\ArgonInput;
use Maduser\Argon\Console\ArgonOutput;
use Maduser\Argon\Console\Exception\ExceptionFormatter;
use Maduser\Argon\Contracts\Console\InputInterface;
use Maduser\Argon\Contracts\Console\ConsoleInterface;
use Maduser\Argon\Contracts\Console\OutputInterface;
use Maduser\Argon\Console\Factory\ConsolePipelineFactory;
use Maduser\Argon\Console\ConsolePipeline;
use Maduser\Argon\Console\Middleware\CommandDispatcher;
use Maduser\Argon\Console\SymfonyConsoleAdapter;
use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Console\CliKernel;
use Maduser\Argon\Contracts\Console\Exception\ExceptionFormatterInterface;
use Maduser\Argon\Contracts\KernelInterface;
use Maduser\Argon\Logging\LoggerFactory;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

class ArgonCliFoundation extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $parameters = $container->getParameters();

        if (class_exists('Monolog\Logger') && class_exists('Monolog\Handler\StreamHandler')) {
            $container->set(LoggerFactory::class, args: [
                'logLevel' => $parameters->get('logLevel', 400),
                'logFile' => $parameters->get('logFile', null),
            ]);

            $container->set(LoggerInterface::class, Logger::class)
                ->factory(LoggerFactory::class, 'create')
                ->tag('logger');
        } else {
            $container->set(LoggerInterface::class, NullLogger::class);
        }

        $container->set(ExceptionFormatterInterface::class, ExceptionFormatter::class);

        $container->set(ConsolePipeline::class)
            ->factory(ConsolePipelineFactory::class, 'create')
            ->tag(['cli.pipeline']);

        $container->set(CommandDispatcher::class)
            ->tag(['middleware.cli']);

        $container->set(InputInterface::class, ArgonInput::class, [
            'argv' => $_SERVER['argv'] ?? throw new RuntimeException('No CLI arguments provided.')
        ])->tag(['console.input']);

        $container->set(OutputInterface::class, ArgonOutput::class)
            ->tag(['console.output']);

        $container->set(ConsoleInterface::class, ArgonConsole::class)
            ->tag(['console.adapter']);

        $container->set(KernelInterface::class, CliKernel::class);
    }
}
