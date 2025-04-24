<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Middleware;

use Maduser\Argon\Contracts\Http\Server\Middleware\ResponseResponderInterface;
use Maduser\Argon\Contracts\Support\ResultContextInterface;
use Maduser\Argon\Support\ResultContext;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class ResponseResponder implements MiddlewareInterface, ResponseResponderInterface
{
    public function __construct(
        private ResultContextInterface $result,
        private LoggerInterface $logger,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->result->is(ResponseInterface::class)) {
            $this->logger->info(get_class($this) . ' forwards a response');

            /** @var ResponseInterface */
            return $this->result->get();
        }

        return $handler->handle($request);
    }
}
