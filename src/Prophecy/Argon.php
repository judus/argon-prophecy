<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy;

use Closure;
use ErrorException;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use ReflectionException;

final class Argon
{
    /**
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws ContainerException
     */
    public static function boot(Closure $callback): void
    {
        (new Application())
            ->register($callback)
            ->handle();
    }
}
