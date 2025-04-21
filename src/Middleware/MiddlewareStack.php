<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware;

final readonly class MiddlewareStack
{
    /**
     * @param list<class-string> $middlewares
     */
    public function __construct(
        private array $middlewares = []
    ) {
    }

    /**
     * A deterministic, order-sensitive hash key.
     */
    public function getId(): string
    {
        return 'pipeline__' . md5(json_encode($this->middlewares));
    }

    /**
     * @return list<class-string>
     */
    public function toArray(): array
    {
        return $this->middlewares;
    }
}
