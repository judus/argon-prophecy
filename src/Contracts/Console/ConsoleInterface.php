<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Console;

interface ConsoleInterface
{
    public function run(): int;
}
