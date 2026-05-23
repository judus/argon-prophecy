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
    private const EXPLICIT_HANDLER = AppHandlerInterface::class;

    /**
     * @var list<class-string>
     */
    private const LIFECYCLE_HANDLERS = [
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
        if ($container->has(self::EXPLICIT_HANDLER)) {
            return $this->resolveRegisteredHandler($container, self::EXPLICIT_HANDLER);
        }

        $registeredHandlerIds = $this->findRegisteredLifecycleHandlers($container);

        if ($registeredHandlerIds === []) {
            throw new RuntimeException('No application handler registered.');
        }

        if (count($registeredHandlerIds) > 1) {
            throw new RuntimeException(sprintf(
                'Multiple application handlers registered (%s). Bind %s to choose the active handler.',
                implode(', ', $registeredHandlerIds),
                AppHandlerInterface::class
            ));
        }

        return $this->resolveRegisteredHandler($container, $registeredHandlerIds[0]);
    }

    /**
     * @return list<class-string>
     */
    private function findRegisteredLifecycleHandlers(ArgonContainer $container): array
    {
        $registeredHandlerIds = [];

        foreach (self::LIFECYCLE_HANDLERS as $id) {
            if ($container->has($id)) {
                $registeredHandlerIds[] = $id;
            }
        }

        return $registeredHandlerIds;
    }

    /**
     * @param class-string $id
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function resolveRegisteredHandler(ArgonContainer $container, string $id): AppHandlerInterface
    {
        $handler = $container->get($id);

        if (!$handler instanceof AppHandlerInterface) {
            throw new RuntimeException(sprintf(
                'Application handler binding %s must resolve to %s; got %s.',
                $id,
                AppHandlerInterface::class,
                $handler::class
            ));
        }

        return $handler;
    }
}
