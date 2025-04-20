<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Console;

use Maduser\Argon\Contracts\Console\InputInterface;
use Maduser\Argon\Contracts\Console\OutputInterface;

interface CommandInterface
{
    public function handle(InputInterface $input, OutputInterface $output): int;

    public static function name(): string;
    public static function description(): string;
}
