<?php

namespace Tests\Integration\Mocks;

use Exception;
use LogicException;
use Maduser\Argon\Contracts\Http\Server\Middleware\DispatcherInterface;
use Maduser\Argon\Contracts\Http\Server\ResultContextInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final readonly class FakeExceptionDispatcher implements MiddlewareInterface, DispatcherInterface
{
    public function __construct(private ResultContextInterface $result)
    {
    }

    /**
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->dispatch($request);
        return $handler->handle($request);
    }

    /**
     * @throws Exception
     */
    public function dispatch(ServerRequestInterface $request): void
    {
        $path = $request->getUri()->getPath();

        match ($path) {
            '/throws-runtime' => throw new RuntimeException('Fake runtime exception'),
            '/throws-logic' => throw new LogicException('Fake logic exception'),
            '/throws-generic' => throw new Exception('Generic fake exception'),
            default => sleep(0), // No-op for routes we don't explicitly break
        };
    }
}