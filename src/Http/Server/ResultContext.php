<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server;

use Closure;
use Maduser\Argon\Contracts\Http\Server\ResultContextInterface;
use Psr\Http\Message\ResponseInterface;

final class ResultContext implements ResultContextInterface
{
    private mixed $result = null;

    /**
     * @api
     */
    public function set(mixed $result): self
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @api
     */
    public function get(): mixed
    {
        return $this->result;
    }

    /**
     * @api
     */
    public function has(): bool
    {
        return $this->result !== null;
    }

    /**
     * @api
     */
    public function is(string $type): bool
    {
        return $this->result instanceof $type;
    }

    /**
     * @api
     */
    public function isString(): bool
    {
        return is_string($this->result);
    }

    /**
     * @api
     */
    public function isScalar(): bool
    {
        return is_scalar($this->result);
    }

    /**
     * @api
     */
    public function isClosure(): bool
    {
        return $this->result instanceof Closure;
    }

    /**
     * @api
     */
    public function isResponse(): bool
    {
        return $this->result instanceof ResponseInterface;
    }

    /**
     * @api
     */
    public function isArray(): bool
    {
        return is_array($this->result);
    }

    /**
     * @api
     */
    public function isObject(): bool
    {
        return is_object($this->result);
    }

    /**
     * @api
     */
    public function isCallable(): bool
    {
        return is_callable($this->result);
    }
}
