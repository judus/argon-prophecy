<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Console;

interface InputInterface
{
    public function getArguments(): array;

    public function getArgument(string $name): mixed;

    public function getOption(string $name): mixed;

    public function hasArgument(string $name): bool;

    public function hasOption(string $name): bool;

    public function getFirstArgument(): ?string;
}
