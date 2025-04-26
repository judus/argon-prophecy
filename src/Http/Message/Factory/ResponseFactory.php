<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Message\Factory;

use JsonException;
use Maduser\Argon\Http\Message\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class ResponseFactory implements ResponseFactoryInterface
{
    public function __construct(
        private StreamFactoryInterface $streamFactory
    ) {}

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new Response(
            body: $this->streamFactory->createStream(),
            status: $code,
            reasonPhrase: $reasonPhrase
        );
    }

    public function text(string $content, int $status = 200): ResponseInterface
    {
        return $this->createResponse($status)
            ->withHeader('Content-Type', 'text/plain')
            ->withBody($this->streamFactory->createStream($content));
    }

    public function html(string $content, int $status = 200): ResponseInterface
    {
        return $this->createResponse($status)
            ->withHeader('Content-Type', 'text/html')
            ->withBody($this->streamFactory->createStream($content));
    }

    /**
     * @throws JsonException
     */
    public function json(array|object $data, int $status = 200): ResponseInterface
    {
        return $this->createResponse($status)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode($data, JSON_THROW_ON_ERROR)));
    }
}

