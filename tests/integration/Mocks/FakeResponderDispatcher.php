<?php

declare(strict_types=1);

namespace Tests\Integration\Mocks;

use Maduser\Argon\Contracts\Http\Server\Middleware\DispatcherInterface;
use Maduser\Argon\Contracts\Http\Server\ResultContextInterface;
use Maduser\Argon\Http\Message\Response;
use Maduser\Argon\Http\Message\Stream;
use Maduser\Argon\Support\Html;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class FakeResponderDispatcher implements MiddlewareInterface, DispatcherInterface
{
    public function __construct(private ResultContextInterface $result)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->dispatch($request);
        return $handler->handle($request);
    }

    public function dispatch(ServerRequestInterface $request): void
    {
        $path = $request->getUri()->getPath();

        match ($path) {
            '/json' => $this->result->set(['key' => 'value']),
            '/json-serializable' => $this->result->set(new FakeJsonSerializable()),
            '/html' => $this->result->set(Html::create('<p>HTML Mock</p>')),
            '/plain' => $this->result->set('Plain text response.'),
            '/response' => $this->result->set(
                new Response(new Stream('Real Response'), 200, ['Content-Type' => 'text/plain'])
            ),
            default => sleep(0),
        };
    }
}
