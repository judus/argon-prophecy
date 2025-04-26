<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Middleware;

use Maduser\Argon\Contracts\Http\Server\Middleware\PlainTextResponderInterface;
use Maduser\Argon\Contracts\Http\Server\ResultContextInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class PlainTextResponder extends AbstractResponder implements PlainTextResponderInterface
{
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        private ResultContextInterface $result,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($responseFactory, $streamFactory, $logger);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->result->isString()) {
            return $this->createResponse((string) $this->result->get(), 'text/plain; charset=UTF-8');
        }

        return $handler->handle($request);
    }
}
