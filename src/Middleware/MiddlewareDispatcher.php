<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware;

use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Exception\MiddlewareException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class MiddlewareDispatcher implements RequestHandlerInterface
{
    private int $index = 0;

    /**
     * @param list<class-string<MiddlewareInterface>|MiddlewareInterface> $middleware
     */
    public function __construct(
        private readonly array $middleware,
        private readonly MiddlewareResolverInterface $resolver,
        private readonly ?RequestHandlerInterface $finalHandler,
        private readonly LoggerInterface $logger,
        private readonly int $verbosity = MiddlewareVerbosity::NORMAL,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($this->middleware[$this->index])) {
            if ($this->finalHandler !== null) {
                if ($this->verbosity >= MiddlewareVerbosity::DEBUG) {
                    $this->logger->info('Final handler invoked');
                }
                return $this->finalHandler->handle($request);
            }

            throw new MiddlewareException('No middleware returned a response, and no final handler is set.');
        }

        $entry = $this->middleware[$this->index++];

        if (is_string($entry)) {
            $middleware = $this->resolver->resolve($entry);
        } elseif ($entry instanceof MiddlewareInterface) {
            $middleware = $entry;
        } else {
            throw new MiddlewareException(sprintf(
                'Invalid middleware entry at index %d. Must be class-string or MiddlewareInterface, got: %s',
                $this->index - 1,
                get_debug_type($entry)
            ));
        }

        if ($this->verbosity >= MiddlewareVerbosity::NORMAL) {
            $this->logger->info('Executing middleware', ['middleware' => $middleware]);
        }

        return $middleware->process($request, $this);
    }
}
