<?php

declare(strict_types=1);

namespace Maduser\Argon\View\Middleware;

use Maduser\Argon\View\Contracts\HtmlableInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class HtmlResponderMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ResponseInterface      $response,
        private StreamFactoryInterface $streamFactory
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $request->getAttribute('rawResult');

        if ($result instanceof HtmlableInterface) {
            return $this->respondWithHtml($result->toHtml());
        }

        // Pass through to next middleware
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
