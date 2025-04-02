<?php

declare(strict_types=1);

namespace Maduser\Argon\Kernel\Contracts;

interface KernelInterface
{
    public function setup(): void;

    public function boot(): void;

    public function handle(): void;

    public function terminate(): void;
}
