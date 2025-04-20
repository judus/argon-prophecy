<?php

declare(strict_types=1);

namespace Maduser\Argon\Console\Middleware;

use Maduser\Argon\Console\ConsoleHelpService;
use Maduser\Argon\Contracts\Console\CommandInterface;
use Maduser\Argon\Contracts\Console\InputInterface;
use Maduser\Argon\Contracts\Console\Middleware\MiddlewareInterface;
use Maduser\Argon\Contracts\Console\OutputInterface;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use RuntimeException;

final readonly class CommandDispatcher implements MiddlewareInterface
{
    public function __construct(
        private ArgonContainer $container
    ) {
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function process(InputInterface $input, OutputInterface $output, callable $next): int
    {
        $commandName = $input->getFirstArgument();

        if ($commandName === null) {
            $this->container->get(ConsoleHelpService::class)
                ->printCommandList($output);
            return 0;
        }

        if ($input->hasOption('help')) {
            $this->container->get(ConsoleHelpService::class)
                ->printCommandHelp($input, $output);
            return 0;
        }


        $command = $this->resolveCommand($commandName);

        return $command->handle($input, $output);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function resolveCommand(string $name): CommandInterface
    {
        foreach ($this->container->getTaggedIds('cli.command') as $id) {
            /** @var CommandInterface $class */
            $class = $this->container->getDescriptor($id)?->getConcrete();

            if (is_subclass_of($class, CommandInterface::class) && $class::name() === $name) {
                /** @var CommandInterface $instance */
                $instance = $this->container->get($id);
                return $instance;
            }
        }

        throw new RuntimeException("No CLI command matched: $name");
    }
}
