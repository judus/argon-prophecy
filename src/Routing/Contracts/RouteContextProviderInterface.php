<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing\Contracts;

interface RouteContextProviderInterface
{
    public function getRoute(): ResolvedRouteInterface;
}
