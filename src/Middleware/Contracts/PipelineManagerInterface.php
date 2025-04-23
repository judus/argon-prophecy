<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Contracts;

use Maduser\Argon\Contracts\MiddlewareStackInterface;

interface PipelineManagerInterface
{
    public function register(MiddlewareStackInterface $stack): void;
}
