<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpKernelInterface extends AppHandlerInterface
{
    public function handle(?ServerRequestInterface $request = null): void;

    public function process(?ServerRequestInterface $request = null): ResponseInterface;

    public function emit(ResponseInterface $response): void;
}
