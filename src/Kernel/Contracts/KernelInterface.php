<?php

declare(strict_types=1);

namespace Maduser\Argon\Kernel\Contracts;

interface KernelInterface
{
    /**
     * PSR-15
     */
    public function handle(): void;

    public function setup(): void;

    public function boot(): void;

    public function terminate(): void;
}
