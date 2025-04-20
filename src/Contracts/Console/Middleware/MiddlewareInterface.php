<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Console\Middleware;

use Maduser\Argon\Contracts\Console\InputInterface;
use Maduser\Argon\Contracts\Console\OutputInterface;

interface MiddlewareInterface
{
    public function process(InputInterface $input, OutputInterface $output, callable $next): int;
}
