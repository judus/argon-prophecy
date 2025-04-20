<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class PlainTextResponder implements MiddlewareInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $request->getAttribute('rawResult');

        if (is_scalar($result) || $result === null) {
            return $this->responseFactory
                ->createResponse()
                ->withHeader('Content-Type', 'text/plain; charset=UTF-8')
                ->withBody($this->streamFactory->createStream((string) $result));
        }

        return $handler->handle($request);
    }
}
