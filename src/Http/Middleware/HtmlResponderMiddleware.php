<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Middleware;

//use App\View\ViewTemplate;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HtmlResponderMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ResponseInterface $response,
        private StreamFactoryInterface $streamFactory,
        private mixed $renderer = null, // optional for pure string handling
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $request->getAttribute('rawResult');

        if (is_string($result)) {
            $body = $this->streamFactory->createStream($result);
            return $this->response
                ->withHeader('Content-Type', 'text/html')
                ->withBody($body);
        }

//        if ($result instanceof ViewTemplate && $this->renderer !== null) {
//            $html = ($this->renderer)($result->template, $result->data);
//            $body = $this->streamFactory->createStream($html);
//            return $this->response
//                ->withHeader('Content-Type', 'text/html')
//                ->withBody($body);
//        }

        return $handler->handle($request);
    }
}
