<?php

namespace App\Middlewares;

use Maduser\Argon\Http\Factory\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DumbMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getQueryParams()['fail'] ?? false) {
            return ResponseFactory::text('Short-circuited by DumbMiddleware', 200);
        }

        return $handler->handle($request);
    }
}
