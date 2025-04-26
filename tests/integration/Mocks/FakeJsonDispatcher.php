<?php

declare(strict_types=1);

namespace Tests\Integration\Mocks;

use Maduser\Argon\Contracts\Http\Server\ResultContextInterface;
use Maduser\Argon\Contracts\Http\Server\Middleware\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class FakeJsonDispatcher implements MiddlewareInterface, DispatcherInterface
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
        $this->result->set([
            'status' => 'ok',
        ]);
    }


}
