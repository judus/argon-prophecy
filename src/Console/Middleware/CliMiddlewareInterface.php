<?php

declare(strict_types=1);

namespace Maduser\Argon\Console\Middleware;

use Maduser\Argon\Console\Contracts\ConsoleInputInterface;
use Maduser\Argon\Console\Contracts\ConsoleOutputInterface;

interface CliMiddlewareInterface
{
    public function process(ConsoleInputInterface $input, ConsoleOutputInterface $output, callable $next): int;
}
