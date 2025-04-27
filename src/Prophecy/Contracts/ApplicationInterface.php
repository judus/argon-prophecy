<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Contracts;

use Closure;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

interface ApplicationInterface
{
    public function register(Closure $closure): self;

    public function handle(?ServerRequestInterface $request = null): void;
    public function process(?ServerRequestInterface $request = null): ResponseInterface;
    public function emit(ResponseInterface $response): void;
}
