<?php

declare(strict_types=1);

namespace Maduser\Argon\Console\Contracts;

interface CommandInterface
{
    public function handle(ConsoleInputInterface $input, ConsoleOutputInterface $output): int;

    public function getName(): string;
}
