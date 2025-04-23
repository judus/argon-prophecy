<?php

namespace Maduser\Argon\Contracts;

interface MiddlewareStackInterface
{
    /**
     * A deterministic, order-sensitive hash key.
     */
    public function getId(): string;

    /**
     * @return list<class-string>
     */
    public function toArray(): array;
}