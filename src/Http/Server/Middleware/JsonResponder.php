<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Middleware;

use JsonException;
use JsonSerializable;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class JsonResponder implements MiddlewareInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory
    ) {
    }

    /**
     * @throws JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var null|object|string|array $result */
        $result = $request->getAttribute('rawResult');

        if (is_array($result) || $result instanceof JsonSerializable) {
            if ($result instanceof JsonSerializable) {
                /** @var null|object|string|array $result */
                $result = $result->jsonSerialize();
            }

            $json = json_encode($result, JSON_THROW_ON_ERROR);

            return $this->responseFactory
                ->createResponse()
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream($json));
        }

        return $handler->handle($request);
    }
}
