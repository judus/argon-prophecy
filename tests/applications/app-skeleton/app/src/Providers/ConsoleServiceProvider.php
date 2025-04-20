<?php

namespace App\Providers;

use App\Console\GreetCommand;
use App\Console\SymfonyCommand;
use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;

class ConsoleServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $container->set(SymfonyCommand::class)
            ->tag(['cli.command']);

        $container->set(GreetCommand::class)
            ->tag(['cli.command']);
    }
}