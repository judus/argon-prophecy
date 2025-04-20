<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server;

use Maduser\Argon\Http\Message\Factory\ResponseFactory;
use Maduser\Argon\Http\Message\Stream;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class MiddlewarePipeline implements RequestHandlerInterface
{
    /** @var MiddlewareInterface[] */
    private array $middleware = [];

    private RequestHandlerInterface $finalHandler;

    public function __construct(
        private readonly LoggerInterface $logger,
        ?RequestHandlerInterface $finalHandler = null,
    ) {
        $this->finalHandler = $finalHandler ?? new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $result = $request->getAttribute('rawResult');

                if ($result instanceof ResponseInterface) {
                    return $result;
                }

                throw new RuntimeException('No middleware produced a valid response.');
            }
        };
    }

    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;

        $this->logger->info('Middleware registered', ['class' => get_class($middleware)]);

        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createHandler(0)->handle($request);
    }

    private function createHandler(int $index): RequestHandlerInterface
    {
        if (!isset($this->middleware[$index])) {
            $this->logger->info('Executing final middleware');
            return $this->finalHandler;
        }

        $this->logger->info('Executing middleware', ['middleware' => $this->middleware[$index]]);

        return new class (
            $this->middleware[$index],
            $this->createHandler($index + 1)
        ) implements RequestHandlerInterface {
            public function __construct(
                private readonly MiddlewareInterface $middleware,
                private readonly RequestHandlerInterface $next
            ) {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->middleware->process($request, $this->next);
            }
        };
    }
}
