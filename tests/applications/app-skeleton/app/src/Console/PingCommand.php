<?php

declare(strict_types=1);

namespace App\Console;

use Maduser\Argon\Contracts\Console\CommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PingCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'ping';
    }

    public function handle(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Pong!</info>');
        return 0;
    }
}
