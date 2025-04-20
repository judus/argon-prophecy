<?php

declare(strict_types=1);

namespace Maduser\Argon\Console;

use Maduser\Argon\Contracts\Console\ConsoleInterface;
use Maduser\Argon\Contracts\Console\InputInterface;
use Maduser\Argon\Contracts\Console\OutputInterface;
use Maduser\Argon\Console\ConsolePipeline;

final readonly class ArgonConsole implements ConsoleInterface
{
    public function __construct(
        private ConsolePipeline        $pipeline,
        private InputInterface         $input,
        private OutputInterface $output
    ) {
    }

    public function run(): int
    {
        return $this->pipeline->handle($this->input, $this->output);
    }
}
