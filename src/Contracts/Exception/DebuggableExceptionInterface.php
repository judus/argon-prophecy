<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Exception;

use Throwable;

interface DebuggableExceptionInterface extends Throwable
{
    /**
     * Indicates if the exception can be safely shown to the client.
     *
     * @return bool
     */
    public function isSafeToDisplay(): bool;
}
