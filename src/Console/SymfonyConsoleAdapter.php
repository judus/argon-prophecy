<?php

declare(strict_types=1);

namespace Maduser\Argon\Console;

use Exception;
use Maduser\Argon\Console\Contracts\ConsoleInterface;
use Maduser\Argon\Container\ArgonContainer;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

final class SymfonyConsoleAdapter implements ConsoleInterface
{
    public function __construct(private ArgonContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @throws Exception
     */
    public function run(): int
    {
        $app = new Application('Argon CLI', '1.0.0');

        foreach ($this->container->getTaggedIds('cli.command') as $id) {
            $command = $this->container->get($id);
            if ($command instanceof Command) {
                $app->add($command);
            }
        }

        // Optionally: $app->add(...), or autoload commands.
        return $app->run();
    }
}
