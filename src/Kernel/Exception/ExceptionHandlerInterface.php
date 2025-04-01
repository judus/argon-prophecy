<?php

declare(strict_types=1);

namespace Maduser\Argon\Kernel\Exception;

use Psr\Http\Message\ResponseInterface;
use Throwable;

interface ExceptionHandlerInterface
{
    /**
     * Report/log the throwable — should never throw itself.
     */
    public function report(Throwable $throwable): void;

    /**
     * Convert the throwable into a Response.
     */
    public function render(Throwable $throwable): ResponseInterface;
}
