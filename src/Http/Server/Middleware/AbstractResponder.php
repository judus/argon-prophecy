<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;

abstract readonly class AbstractResponder
{
    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
        protected StreamFactoryInterface $streamFactory,
        protected ?LoggerInterface $logger = null,
    ) {
    }

    protected function createResponse(string $bodyContent, string $contentType): ResponseInterface
    {
        $this->logger?->info('Creating response', [
            'class' => static::class,
            'type' => $contentType
        ]);

        return $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', $contentType)
            ->withBody($this->streamFactory->createStream($bodyContent));
    }
}
