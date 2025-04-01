<?php

declare(strict_types=1);

namespace Maduser\Argon\Console\Contracts;

interface ConsoleInterface
{
    public function run(): int;
}
