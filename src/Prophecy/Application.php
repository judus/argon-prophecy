<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy;

use Closure;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Compiler\ContainerCompiler;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Prophecy\Contracts\ApplicationInterface;
use Maduser\Argon\Contracts\Handler\AppHandlerInterface;
use Maduser\Argon\Contracts\Handler\CliKernelInterface;
use Maduser\Argon\Contracts\Handler\HttpKernelInterface;
use Maduser\Argon\Contracts\KernelInterface;
use Maduser\Argon\Prophecy\ErrorHandling\BootstrapErrorHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use ReflectionException;
use RuntimeException;
use Throwable;

final class Application implements ApplicationInterface
{
    protected ?ArgonContainer $container = null;
    protected ?LoggerInterface $logger = null;
    private ?Closure $configureContainer = null;
    private ?string $compiledFilePath = null;
    private ?string $compiledClass = null;
    private ?string $compiledNamespace = null;
    private ?AppHandlerInterface $handler = null;

    public function __construct(
        ?ArgonContainer $container = null,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger;
        (new BootstrapErrorHandler($this->logger))->register();
        $this->container = $container;
    }

    public function register(Closure $closure): self
    {
        $this->configureContainer = $closure;
        return $this;
    }

    public function compile(string $filePath, string $className, string $namespace = ''): self
    {
        $this->compiledFilePath = $filePath;
        $this->compiledClass = $className;
        $this->compiledNamespace = $namespace;
        return $this;
    }

    /**
     * @throws ReflectionException
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function handle(?ServerRequestInterface $request = null): void
    {
        $handler = $this->bootstrap();

        if ($handler instanceof HttpKernelInterface) {
            $handler->handle($request);
            return;
        }

        $exitCode = $handler->run();
        $handler->terminate($exitCode);
    }

    /**
     * @throws ReflectionException
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function process(?ServerRequestInterface $request = null): ResponseInterface
    {
        $handler = $this->bootstrap();

        if (!$handler instanceof HttpKernelInterface) {
            throw new RuntimeException('Active handler does not support HTTP processing.');
        }

        return $handler->process($request);
    }

    /**
     * @throws ReflectionException
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function emit(ResponseInterface $response): void
    {
        $handler = $this->bootstrap();

        if (!$handler instanceof HttpKernelInterface) {
            throw new RuntimeException('Active handler does not support HTTP emission.');
        }

        $handler->emit($response);
    }

    /**
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws ContainerException
     */
    private function bootstrap(): AppHandlerInterface
    {
        if ($this->handler !== null) {
            return $this->handler;
        }

        $container = $this->getContainer();

        if ($container->has(LoggerInterface::class)) {
            $this->logger = $container->get(LoggerInterface::class);
        }

        $this->logContainerLoadedEvent();
        $container->boot();
        $this->logContainerBootedEvent();

        $handler = $this->resolveHandler($container);

        $this->logHandlerReadyEvent($handler);

        return $handler;
    }

    /**
     * @throws ReflectionException
     * @throws ContainerException
     */
    private function getContainer(): ArgonContainer
    {
        if ($this->container) {
            return $this->container;
        }

        if ($compiled = $this->loadCompiledContainer()) {
            return $this->container = $compiled;
        }

        return $this->container = $this->buildContainer();
    }

    /**
     * @throws ReflectionException
     * @throws ContainerException
     */
    private function buildContainer(): ArgonContainer
    {
        $container = new ArgonContainer();
        $container->getParameters()->set('basePath', $this->getBasePath());

        if ($this->configureContainer !== null) {
            ($this->configureContainer)($container);
        }

        $this->container = $container;
        $this->compileIfConfigured();

        return $container;
    }

    /**
     * @throws ReflectionException
     * @throws ContainerException
     */
    private function compileIfConfigured(): void
    {
        if ($this->container === null || $this->compiledFilePath === null || $this->compiledClass === null) {
            return;
        }

        $compiler = new ContainerCompiler($this->container);
        $compiler->compile(
            $this->compiledFilePath,
            $this->compiledClass,
            $this->compiledNamespace ?? ''
        );
    }

    private function loadCompiledContainer(): ?ArgonContainer
    {
        if (
            $this->compiledFilePath === null ||
            $this->compiledClass === null ||
            !file_exists($this->compiledFilePath)
        ) {
            return null;
        }

        $this->logger?->info('Loading compiled container...');

        /** @psalm-suppress UnresolvableInclude */
        require_once $this->compiledFilePath;

        $fqcn = $this->compiledNamespace !== null
            ? $this->compiledNamespace . '\\' . $this->compiledClass
            : $this->compiledClass;

        if (!class_exists($fqcn)) {
            throw new RuntimeException("Compiled container class '$this->compiledClass' not found.");
        }

        /** @psalm-suppress MixedMethodCall */
        $container = new $fqcn();
        if (!$container instanceof ArgonContainer) {
            throw new RuntimeException("Compiled container must extend ArgonContainer.");
        }

        $this->logger?->info('Compiled container loaded.');

        return $container;
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function resolveHandler(ArgonContainer $container): AppHandlerInterface
    {
        $candidates = [
            AppHandlerInterface::class,
            HttpKernelInterface::class,
            CliKernelInterface::class,
            KernelInterface::class,
        ];

        foreach ($candidates as $id) {
            if (!$container->has($id)) {
                continue;
            }

            $handler = $container->get($id);
            if ($handler instanceof AppHandlerInterface) {
                return $this->handler = $handler;
            }
        }

        throw new RuntimeException('No application handler registered.');
    }

    private function getBasePath(): string
    {
        return dirname($_SERVER['SCRIPT_FILENAME'] ?? __DIR__, 2);
    }

    private function logContainerLoadedEvent(): void
    {
        if ($this->logger && $this->container) {
            $this->logger->info('Container loaded.', [
                'class' => get_class($this->container),
            ]);

            $this->logContainerDebugInfo('loaded');
        }
    }

    private function logContainerBootedEvent(): void
    {
        if ($this->logger && $this->container) {
            $this->logger->info('Container booted.', [
                'class' => get_class($this->container),
            ]);

            $this->logContainerDebugInfo('booted');
        }
    }

    private function logHandlerReadyEvent(AppHandlerInterface $handler): void
    {
        if ($this->logger && $this->container) {
            $this->logger->info('Application handler resolved.', [
                'class' => get_class($handler),
            ]);

            $this->logContainerDebugInfo('handler_ready');
        }
    }

    private function logContainerDebugInfo(string $stage): void
    {
        if ($this->logger && $this->container) {
            $info = [
                'parameters'       => $this->container->getParameters()->all(),
                'bindings'         => $this->container->getBindings(),
                'preInterceptors'  => $this->container->getPreInterceptors(),
                'postInterceptors' => $this->container->getPostInterceptors(),
            ];

            if (
                get_class($this->container) !== ArgonContainer::class &&
                method_exists($this->container, 'getServiceMap')
            ) {
                $info['compiled'] = true;
                try {
                    $info['serviceMap'] = (array) $this->container->getServiceMap();
                } catch (Throwable) {
                    $info['serviceMap'] = ['error' => 'Could not fetch service map'];
                }
            } else {
                $info['compiled'] = false;
            }

            $this->logger->debug("Container [$stage] debug info:", $info);
        }
    }
}
