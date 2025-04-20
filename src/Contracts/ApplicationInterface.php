<?php

namespace Maduser\Argon\Contracts;

use Closure;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use ReflectionException;

interface ApplicationInterface
{
    public function register(Closure $closure): self;

    /**
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function handle(): void;
}