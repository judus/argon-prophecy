<?php

declare(strict_types=1);

namespace App\Console;

use Maduser\Argon\Contracts\Console\CommandInterface;
use Maduser\Argon\Contracts\Console\InputInterface;
use Maduser\Argon\Contracts\Console\OutputInterface;

final class GreetCommand implements CommandInterface
{
    public static function name(): string
    {
        return 'greet';
    }

    public static function description(): string
    {
        return 'Greet command';
    }

    public function handle(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) ($input->getArgument('name') ?? 'World');
        $output->write("Hello $name!");

        return 0;
    }
}
