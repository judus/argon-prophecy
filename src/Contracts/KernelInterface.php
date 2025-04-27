<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface KernelInterface
{
    /**
     * Handles the full HTTP request lifecycle.
     *
     * @param ServerRequestInterface|null $request
     */
    public function handle(?ServerRequestInterface $request = null): void;

    /**
     * Captures the PSR-7 Response without emitting or terminating.
     *
     * @param ServerRequestInterface|null $request
     * @return ResponseInterface
     */
    public function process(?ServerRequestInterface $request = null): ResponseInterface;

    /**
     * Emits the provided PSR-7 Response to the client.
     *
     * @param ResponseInterface $response
     */
    public function emit(ResponseInterface $response): void;

    /**
     * Terminates the current lifecycle with an exit code.
     *
     * @param int $code Exit code (0 for success, 1+ for failure)
     * @param bool $shouldExit If false, do not actually call exit(); useful for testing
     */
    public function terminate(int $code, bool $shouldExit = true): void;
}
