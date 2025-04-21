<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing\Contracts;

use Psr\Http\Message\ServerRequestInterface;

interface RouteContextInterface
{
    public function set(ServerRequestInterface $request, MatchedRouteInterface $route): ServerRequestInterface;

    public function get(ServerRequestInterface $request): MatchedRouteInterface;

    public function tryGet(ServerRequestInterface $request): ?MatchedRouteInterface;
}
