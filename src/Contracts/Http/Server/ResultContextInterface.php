<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Http\Server;

interface ResultContextInterface
{
    /**
     * @api
     */
    public function set(mixed $result): ResultContextInterface;

    /**
     * @api
     */
    public function get(): mixed;

    /**
     * @api
     */
    public function has(): bool;

    /**
     * @api
     */
    public function is(string $type): bool;

    /**
     * @api
     */
    public function isString(): bool;

    /**
     * @api
     */
    public function isScalar(): bool;

    /**
     * @api
     */
    public function isClosure(): bool;

    /**
     * @api
     */
    public function isResponse(): bool;

    /**
     * @api
     */
    public function isArray(): bool;

    /**
     * @api
     */
    public function isObject(): bool;

    /**
     * @api
     */
    public function isCallable(): bool;
}
