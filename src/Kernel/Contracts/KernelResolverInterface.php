<?php

declare(strict_types=1);

namespace Maduser\Argon\Kernel\Contracts;

interface KernelResolverInterface
{
    public function resolve(): KernelInterface;
}