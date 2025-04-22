<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class ResponseResponder implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var null|object|string|array $result */
        $result = $request->getAttribute('rawResult');
        if ($result instanceof ResponseInterface) {
            return $result;
        }

        return $handler->handle($request);
    }
}
