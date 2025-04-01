<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\ServiceProviders;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;

class ArgonKernelBindings extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function register(ArgonContainer $container): void
    {
        match (php_sapi_name()) {
            'cli', 'phpdbg' => $container->register(ArgonCliFoundation::class),
            default => $container->register(ArgonHttpFoundation::class),
        };
    }
}
