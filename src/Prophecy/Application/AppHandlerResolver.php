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
use Maduser\Argon\Prophecy\Exceptions\ProphecyException;

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
            throw ProphecyException::noApplicationHandlerRegistered();
        }

        if (count($registeredHandlerIds) > 1) {
            throw ProphecyException::multipleApplicationHandlersRegistered($registeredHandlerIds);
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
            throw ProphecyException::invalidApplicationHandlerBinding($id, $handler);
        }

        return $handler;
    }
}
