<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts;

use Maduser\Argon\Contracts\Handler\HttpKernelInterface;

/** @deprecated since 1.x – use Handler interfaces instead. */
interface KernelInterface extends HttpKernelInterface
{
}

