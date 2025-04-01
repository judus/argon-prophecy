<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Middleware;

use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JsonResponderMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ResponseInterface $response,
        private StreamFactoryInterface $streamFactory
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var null|object|string|array $result */
        $result = $request->getAttribute('rawResult');

        if (is_array($result) || $result instanceof JsonSerializable) {
            if ($result instanceof JsonSerializable) {
                $result = (array) $result->jsonSerialize();
            }

            $body = $this->streamFactory->createStream(
                json_encode($result, JSON_THROW_ON_ERROR)
            );

            return $this->response
                ->withHeader('Content-Type', 'application/json')
                ->withBody($body);
        }

        return $handler->handle($request);
    }
}
