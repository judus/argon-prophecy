<?php

declare(strict_types=1);

namespace Maduser\Argon\Console;

use Maduser\Argon\Console\Contracts\ConsoleInputInterface;

class ArgonConsoleInput implements ConsoleInputInterface
{
    private array $opts = [];

    public function __construct(array $argv)
    {
        $this->opts = [];

        foreach ($argv as $i => $arg) {
            if ($i === 0) {
                continue;
            }

            if (!isset($this->opts['command']) && !str_starts_with($arg, '--')) {
                $this->opts['command'] = $arg;
                continue;
            }

            if (str_starts_with($arg, '--')) {
                [$key, $val] = explode('=', substr($arg, 2), 2) + [1 => true];
                $this->opts[$key] = $val;
            }
        }
    }

    public function getArgument(string $name): mixed
    {
        return $this->opts[$name] ?? null;
    }

    public function hasArgument(string $name): bool
    {
        return array_key_exists($name, $this->opts);
    }

    public function getOption(string $name): mixed
    {
        return $this->getArgument($name); // Same thing
    }

    public function hasOption(string $name): bool
    {
        return $this->hasArgument($name); // Same thing
    }

    public function getArguments(): array
    {
        return $this->opts;
    }

    public function getFirstArgument(): ?string
    {
        return $this->opts['command'] ?? null;
    }
}
