<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Factory;

use Maduser\Argon\Http\Message\Uri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

final class UriFactory implements UriFactoryInterface
{
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
