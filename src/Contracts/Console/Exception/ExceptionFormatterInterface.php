<?php

namespace Maduser\Argon\Contracts\Console\Exception;

use Throwable;

interface ExceptionFormatterInterface
{
    /**
     * Formats and outputs an exception to the terminal.
     *
     * @param Throwable $e
     * @return int Exit code (0-255)
     */
    public function format(Throwable $e): int;
}