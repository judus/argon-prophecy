<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Middleware;

use Maduser\Argon\Contracts\Http\Server\Middleware\PlainTextResponderInterface;
use Maduser\Argon\Contracts\Support\ResultContextInterface;
use Maduser\Argon\Support\ResultContext;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class PlainTextResponder implements MiddlewareInterface, PlainTextResponderInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
        private ResultContextInterface $result,
        private LoggerInterface $logger,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->result->isString()) {
            $this->logger->info(get_class($this) . ' creates a plain text response');

            return $this->responseFactory
                ->createResponse()
                ->withHeader('Content-Type', 'text/plain; charset=UTF-8')
                ->withBody($this->streamFactory->createStream((string) $this->result->get()));
        }

        return $handler->handle($request);
    }
}
