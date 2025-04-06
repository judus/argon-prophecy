<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing\Contracts;

use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

interface RouterInterface
{
    /**
     * Match the request and return a resolved route.
     *
     * @throws RuntimeException if no match is found
     */
    public function match(ServerRequestInterface $request): MatchedRouteInterface;
}
