<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Factory;

use Maduser\Argon\Http\Message\Response;
use Maduser\Argon\Http\Message\Stream;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseFactory
{
    public static function text(string $content, int $status = 200): Response
    {
        return new Response(
            new Stream($content),
            $status,
            ['Content-Type' => ['text/plain']]
        );
    }
}
