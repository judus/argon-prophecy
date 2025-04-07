<?php

declare(strict_types=1);

namespace App\Console;

use Maduser\Argon\Console\Contracts\CommandInterface;
use Maduser\Argon\Console\Contracts\ConsoleInputInterface;
use Maduser\Argon\Console\Contracts\ConsoleOutputInterface;
use Symfony\Component\Console\Command\Command;

final class GreetCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'greet';
    }

    public function handle(ConsoleInputInterface $input, ConsoleOutputInterface $output): int
    {
        $name = (string) ($input->getArgument('name') ?? 'World');
        $output->success("Hello $name!");

        return 0;
    }
}
