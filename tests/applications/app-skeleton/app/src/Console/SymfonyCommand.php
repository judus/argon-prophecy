<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class SymfonyCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('symfony:pride')
            ->setDescription('Does nothing, but does it with Symfony pride.')
            ->addArgument('name', InputArgument::OPTIONAL, 'Your name', 'Nobody')
            ->addOption('yell', null, InputOption::VALUE_NONE, 'SHOUT the output');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');
        $message = "Hello, $name.";

        if ($input->getOption('yell')) {
            $message = strtoupper($message);
        }

        $output->writeln("<info>$message</info>");

        return Command::SUCCESS;
    }
}
