<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Console;

interface OutputInterface
{
    public function write(string $message, bool $newline = true): void;

    public function error(string $message): void;

    public function success(string $message): void;

    public function warning(string $message): void;
}
