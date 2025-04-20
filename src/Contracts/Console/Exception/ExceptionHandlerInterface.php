<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Console\Exception;

use Throwable;

interface ExceptionHandlerInterface
{
    public function handle(Throwable $e): int;
}
