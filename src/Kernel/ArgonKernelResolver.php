<?php

declare(strict_types=1);

namespace Maduser\Argon\Kernel;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Kernel\Contracts\KernelInterface;
use Maduser\Argon\Kernel\Contracts\KernelResolverInterface;
use Maduser\Argon\Prophecy\ServiceProviders\ArgonCliFoundation;
use Maduser\Argon\Prophecy\ServiceProviders\ArgonHttpFoundation;

final readonly class ArgonKernelResolver implements KernelResolverInterface
{
    public function __construct(private ArgonContainer $container)
    {
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function resolve(): KernelInterface
    {
        return match (php_sapi_name()) {
            'cli', 'phpdbg' => $this->getCliKernel(),
            default => $this->getHttpKernel(),
        };
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function getCliKernel(): KernelInterface
    {
        $this->container->register(ArgonCliFoundation::class);

        return $this->container->get(CliKernel::class);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function getHttpKernel(): KernelInterface
    {
        $this->container->register(ArgonHttpFoundation::class);

        return $this->container->get(HttpKernel::class);
    }
}
