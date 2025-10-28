<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Application;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Contracts\Handler\AppHandlerInterface;
use Maduser\Argon\Contracts\Handler\CliKernelInterface;
use Maduser\Argon\Contracts\Handler\HttpKernelInterface;
use Maduser\Argon\Contracts\KernelInterface;
use RuntimeException;

/**
 * Resolves the application handler from the container.
 *
 * @internal
 */
final class AppHandlerResolver
{
    /**
     * @var list<class-string>
     */
    private const CANDIDATES = [
        AppHandlerInterface::class,
        HttpKernelInterface::class,
        CliKernelInterface::class,
        KernelInterface::class,
    ];

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function resolve(ArgonContainer $container): AppHandlerInterface
    {
        foreach (self::CANDIDATES as $id) {
            if (!$container->has($id)) {
                continue;
            }

            $handler = $container->get($id);

            if ($handler instanceof AppHandlerInterface) {
                return $handler;
            }
        }

        throw new RuntimeException('No application handler registered.');
    }
}
