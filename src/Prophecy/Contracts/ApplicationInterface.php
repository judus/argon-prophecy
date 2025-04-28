<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Contracts;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ApplicationInterface
{
    public function register(Closure $closure): self;

    public function handle(?ServerRequestInterface $request = null): void;
    public function process(?ServerRequestInterface $request = null): ResponseInterface;
    public function emit(ResponseInterface $response): void;
}
