<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Middleware;

use Maduser\Argon\Contracts\Http\Server\Middleware\HtmlResponderInterface;
use Maduser\Argon\Contracts\Http\Server\Middleware\HtmlableInterface;
use Maduser\Argon\Contracts\Support\ResultContextInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class HtmlResponder implements MiddlewareInterface, HtmlResponderInterface
{
    public function __construct(
        private ResponseInterface $response,
        private StreamFactoryInterface $streamFactory,
        private ResultContextInterface $result,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var HtmlableInterface $result */
        $result = $this->result->get();

        if ($result instanceof HtmlableInterface) {
            return $this->respondWithHtml($result->toHtml());
        }

        return $handler->handle($request);
    }

    private function respondWithHtml(string $html): ResponseInterface
    {
        $body = $this->streamFactory->createStream($html);

        return $this->response
            ->withHeader('Content-Type', 'text/html; charset=UTF-8')
            ->withBody($body);
    }
}
