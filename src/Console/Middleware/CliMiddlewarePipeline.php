<?php

declare(strict_types=1);

namespace Maduser\Argon\Console\Middleware;

use Maduser\Argon\Console\Contracts\ConsoleInputInterface;
use Maduser\Argon\Console\Contracts\ConsoleOutputInterface;

final class CliMiddlewarePipeline
{
    /** @var CliMiddlewareInterface[] */
    private array $middleware = [];

    public function pipe(CliMiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function handle(ConsoleInputInterface $input, ConsoleOutputInterface $output): int
    {
        return $this->createHandler(0)($input, $output);
    }

    private function createHandler(int $index): callable
    {
        if (!isset($this->middleware[$index])) {
            return fn(ConsoleInputInterface $in, ConsoleOutputInterface $out): int => 0;
        }

        $middleware = $this->middleware[$index];
        $next = $this->createHandler($index + 1);

        return fn(ConsoleInputInterface $in, ConsoleOutputInterface $out): int =>
        $middleware->process($in, $out, $next);
    }
}
