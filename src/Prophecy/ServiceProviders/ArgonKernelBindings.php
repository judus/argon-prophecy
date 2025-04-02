<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\ServiceProviders;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Kernel\ArgonKernelResolver;
use Maduser\Argon\Kernel\Contracts\KernelInterface;
use Maduser\Argon\Kernel\Contracts\KernelResolverInterface;

class ArgonKernelBindings extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $container->singleton(KernelResolverInterface::class, ArgonKernelResolver::class);

    }
}
