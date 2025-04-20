<?php

declare(strict_types=1);

namespace Maduser\Argon\Console;

use Maduser\Argon\Contracts\Console\InputInterface;
use Maduser\Argon\Contracts\Console\Middleware\MiddlewareInterface;
use Maduser\Argon\Contracts\Console\OutputInterface;

final class ConsolePipeline
{
    /** @var MiddlewareInterface[] */
    private array $middleware = [];

    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function handle(InputInterface $input, OutputInterface $output): int
    {
        return $this->createHandler(0)($input, $output);
    }

    private function createHandler(int $index): callable
    {
        if (!isset($this->middleware[$index])) {
            return fn(InputInterface $in, OutputInterface $out): int => 0;
        }

        $middleware = $this->middleware[$index];
        $next = $this->createHandler($index + 1);

        return fn(InputInterface $in, OutputInterface $out): int =>
        $middleware->process($in, $out, $next);
    }
}
