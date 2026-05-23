<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Application;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Contracts\Handler\AppHandlerInterface;
use Throwable;

/**
 * Shared logging helpers for the Application bootstrap process.
 *
 * @internal
 */
trait ApplicationLogging
{
    abstract protected function getContainerInstance(): ?ArgonContainer;

    protected function logContainerLoadedEvent(): void
    {
        $container = $this->getContainerInstance();

        if ($this->logger && $container) {
            $this->logger->info('Container loaded.', [
                'class' => $container::class,
            ]);

            $this->logContainerDebugInfo('loaded', $container);
        }
    }

    protected function logContainerBootedEvent(): void
    {
        $container = $this->getContainerInstance();

        if ($this->logger && $container) {
            $this->logger->info('Container booted.', [
                'class' => $container::class,
            ]);

            $this->logContainerDebugInfo('booted', $container);
        }
    }

    protected function logHandlerReadyEvent(AppHandlerInterface $handler): void
    {
        $container = $this->getContainerInstance();

        if ($this->logger && $container) {
            $this->logger->info('Application handler resolved.', [
                'class' => $handler::class,
            ]);

            $this->logContainerDebugInfo('handler_ready', $container);
        }
    }

    private function logContainerDebugInfo(string $stage, ArgonContainer $container): void
    {
        if (!$this->logger) {
            return;
        }

        $info = [
            'class' => $container::class,
            'stage' => $stage,
            'parameterCount' => count($container->getParameters()->all()),
            'bindingCount' => count($container->getBindings()),
            'preInterceptorCount' => count($container->getPreInterceptors()),
            'postInterceptorCount' => count($container->getPostInterceptors()),
        ];

        if ($container::class !== ArgonContainer::class && method_exists($container, 'getServiceMap')) {
            $info['compiled'] = true;
            try {
                $info['serviceMapCount'] = count((array) $container->getServiceMap());
            } catch (Throwable) {
                $info['serviceMapAvailable'] = false;
            }
        } else {
            $info['compiled'] = false;
        }

        $this->logger->debug("Container [$stage] debug info:", $info);
    }
}
