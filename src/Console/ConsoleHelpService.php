<?php

declare(strict_types=1);

namespace Maduser\Argon\Console;

use Maduser\Argon\Contracts\Console\CommandInterface;
use Maduser\Argon\Contracts\Console\InputInterface;
use Maduser\Argon\Contracts\Console\OutputInterface;
use Maduser\Argon\Container\ArgonContainer;

final readonly class ConsoleHelpService
{
    public function __construct(private ArgonContainer $container)
    {
    }

    public function printCommandList(OutputInterface $output): void
    {
        $output->write("\n\e[1mAvailable Commands:\e[0m\n");

        $commands = $this->container->getTaggedIds('cli.command');

        foreach ($commands as $id) {
            $descriptor = $this->container->getDescriptor($id);
            $class = $descriptor?->getConcrete();

            if ($class === null || !is_subclass_of($class, CommandInterface::class)) {
                continue;
            }

            /** @var CommandInterface $class */
            $name = $class::name();
            $description = $class::description();

            $output->write("  \e[32m{$name}\e[0m\t\t{$description}");
        }

        $output->write("\nUse \e[1m<command> --help\e[0m for more info on a command.\n");
    }

    public function printCommandHelp(InputInterface $input, OutputInterface $output): void
    {
        $commandName = $input->getFirstArgument();

        if ($commandName === null) {
            $output->write("\n\e[31mNo command specified for help.\e[0m\n");
            return;
        }

        foreach ($this->container->getTaggedIds('cli.command') as $id) {
            $descriptor = $this->container->getDescriptor($id);
            $class = $descriptor?->getConcrete();

            if ($class === null || !is_subclass_of($class, CommandInterface::class)) {
                continue;
            }

            /** @var CommandInterface $class */
            if ($class::name() === $commandName) {
                $output->write("\n\e[1mHelp for \e[32m{$commandName}\e[0m:\e[0m\n");
                $output->write("  \e[33m{$class::description()}\e[0m\n");
                return;
            }
        }

        $output->write("\n\e[31mNo help found for command: {$commandName}\e[0m\n");
    }
}