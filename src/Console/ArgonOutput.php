<?php

declare(strict_types=1);

namespace Maduser\Argon\Console;

use Maduser\Argon\Contracts\Console\OutputInterface;

class ArgonOutput implements OutputInterface
{
    public function write(string $message, bool $newline = true): void
    {
        echo $message . ($newline ? PHP_EOL : '');
    }

    public function error(string $message): void
    {
        $this->write("\033[31m[ERROR]\033[0m " . $message);
    }

    public function success(string $message): void
    {
        $this->write("\033[32m[SUCCESS]\033[0m " . $message);
    }

    public function warning(string $message): void
    {
        $this->write("\033[33m[WARNING]\033[0m " . $message);
    }
}
