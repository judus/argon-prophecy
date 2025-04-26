<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Exception;

use RuntimeException;
use Throwable;

final class EmptyMiddlewareChainException extends RuntimeException
{
    public function __construct(
        string $message = 'No middleware produced a valid response.',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
