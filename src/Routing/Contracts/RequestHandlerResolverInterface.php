<?php

namespace Maduser\Argon\Routing\Contracts;

use Maduser\Argon\Routing\ResolvedRequestHandler;
use Psr\Http\Message\ServerRequestInterface;

interface RequestHandlerResolverInterface
{
    public function resolve(ServerRequestInterface $request): ResolvedRequestHandler;
}