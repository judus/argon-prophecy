<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use Closure;
use Maduser\Argon\Routing\Contracts\MatchedRouteInterface;

final readonly class MatchedRoute implements MatchedRouteInterface
{
    /**
     * @param class-string|array{0: class-string, 1: string}|Closure $handler
     * @param list<class-string> $middleware
     * @param array<string, scalar> $arguments
     */
    public function __construct(
        private string|array|Closure $handler,
        private string $method = '__invoke',
        private array $middleware = [],
        private array $arguments = []
    ) {}

    public function getHandler(): string|array|Closure
    {
        return $this->handler;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function toArray(): array
    {
        return [
            'handler' => $this->stringifyHandler(),
            'method' => $this->method,
            'middleware' => $this->middleware,
            'arguments' => $this->arguments,
        ];
    }

    public function __toString(): string
    {
        $handler = $this->stringifyHandler();

        return sprintf(
            'MatchedRoute(handler=%s, method=%s, middleware=%d, args=%d)',
            $handler,
            $this->method,
            count($this->middleware),
            count($this->arguments),
        );
    }

    private function stringifyHandler(): string
    {
        return match (true) {
            is_array($this->handler) => implode('@', $this->handler),
            is_string($this->handler) => $this->handler,
            $this->handler instanceof Closure => 'Closure<' . spl_object_id($this->handler) . '>',
            default => 'UnknownHandler',
        };
    }
}
