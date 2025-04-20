<?php

declare(strict_types=1);

namespace Maduser\Argon\Console\Exception;

use Maduser\Argon\Contracts\Console\Exception\ExceptionFormatterInterface;
use Maduser\Argon\Contracts\Exception\DebuggableExceptionInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class ExceptionFormatter implements ExceptionFormatterInterface
{
    public function __construct(
        private bool $debug = false
    ) {
    }

    public function format(Throwable $e): int
    {
        $isDebuggable = $e instanceof DebuggableExceptionInterface && $e->isSafeToDisplay();
        $showTrace = $this->debug || $isDebuggable;

        $output = sprintf(
            "\n\e[1;31m[ERROR]\e[0m %s: %s\n",
            $e::class,
            $e->getMessage()
        );

        if ($showTrace) {
            $output .= "\n" . $e->getTraceAsString() . "\n";
        }

        fwrite(STDERR, $output);

        return $this->resolveExitCode($e);
    }

    private function resolveExitCode(Throwable $e): int
    {
        $code = $e->getCode();

        if (is_int($code) && $code >= 1 && $code <= 255) {
            return $code;
        }

        return 1;
    }
}
