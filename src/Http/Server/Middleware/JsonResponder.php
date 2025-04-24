<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Middleware;

use JsonException;
use JsonSerializable;
use Maduser\Argon\Contracts\Http\Server\Middleware\JsonResponderInterface;
use Maduser\Argon\Contracts\Support\ResultContextInterface;
use Maduser\Argon\Support\ResultContext;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class JsonResponder implements MiddlewareInterface, JsonResponderInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
        private ResultContextInterface $result,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var array|JsonSerializable $raw */
        $raw = $this->result->get();

        if ($this->result->isArray() || $raw instanceof JsonSerializable) {
            $this->logger->info(JsonResponder::class . ' creates a JSON response');

            /** @var array|string|null $data */
            $data = $raw instanceof JsonSerializable
                ? $raw->jsonSerialize()
                : $raw;

            $json = json_encode($data, JSON_THROW_ON_ERROR);

            return $this->responseFactory
                ->createResponse()
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream($json));
        }

        return $handler->handle($request);
    }
}
