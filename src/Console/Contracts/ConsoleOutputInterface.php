<?php

declare(strict_types=1);

namespace Maduser\Argon\Console\Contracts;

interface ConsoleOutputInterface
{
    public function write(string $message, bool $newline = true): void;

    public function error(string $message): void;

    public function success(string $message): void;

    public function warning(string $message): void;
}
