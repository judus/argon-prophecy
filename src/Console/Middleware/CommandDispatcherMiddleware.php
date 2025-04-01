<?php

declare(strict_types=1);

namespace Maduser\Argon\Console\Middleware;

use Maduser\Argon\Console\Contracts\CommandInterface;
use Maduser\Argon\Console\Contracts\ConsoleInputInterface;
use Maduser\Argon\Console\Contracts\ConsoleOutputInterface;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;

final readonly class CommandDispatcherMiddleware implements CliMiddlewareInterface
{
    public function __construct(
        private ArgonContainer $container
    ) {
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function process(ConsoleInputInterface $input, ConsoleOutputInterface $output, callable $next): int
    {
        $commandName = $input->getFirstArgument();

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
            $instance = $this->container->get($id);

            if ($instance instanceof CommandInterface && $instance->getName() === $name) {
                return $instance;
            }
        }

        throw new \RuntimeException("No CLI command matched: $name");
    }
}
