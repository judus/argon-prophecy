<?php

declare(strict_types=1);

namespace Maduser\Argon\Console;

use Maduser\Argon\Console\Contracts\ConsoleInterface;
use Maduser\Argon\Console\Contracts\ConsoleInputInterface;
use Maduser\Argon\Console\Contracts\ConsoleOutputInterface;
use Maduser\Argon\Console\Middleware\CliMiddlewarePipeline;

final readonly class ArgonConsoleAdapter implements ConsoleInterface
{
    public function __construct(
        private CliMiddlewarePipeline $pipeline,
        private ConsoleInputInterface $input,
        private ConsoleOutputInterface $output
    ) {
    }

    public function run(): int
    {
        return $this->pipeline->handle($this->input, $this->output);
    }
}
