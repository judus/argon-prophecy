<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy;

use Closure;
use ErrorException;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Compiler\ContainerCompiler;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Kernel\Contracts\KernelInterface;
use ReflectionException;
use RuntimeException;

final class Application
{
    private ?ArgonContainer $container;
    private ?Closure $callback = null;

    private ?string $compiledFilePath = null;
    private ?string $compiledClass = null;
    private ?string $compiledNamespace = null;

    private bool $booted = false;

    /**
     * @throws ErrorException
     */
    public function __construct(?ArgonContainer $container = null)
    {
        $this->setupErrorHandling();

        $this->container = $container;
    }

    /**
     * @param Closure $callback Must accept ArgonContainer
     */
    public function register(Closure $callback): self
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @throws ReflectionException
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function handle(): void
    {
        if ($this->booted) {
            throw new RuntimeException('Application already booted.');
        }

        $container = $this->getContainer();
        $container->boot();

        $kernel = $this->getKernel($container);

        $kernel->setup();
        $kernel->boot();

        $this->booted = true;

        $kernel->handle();
        $kernel->terminate();
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

        $compiledContainer = $this->loadCompiledContainer();

        if ($compiledContainer !== null) {
            $this->container = $compiledContainer;
            return $this->container;
        }

        $this->container = new ArgonContainer();

        if ($this->callback !== null) {
            ($this->callback)($this->container);
        }

        $this->compileIfConfigured();

        return $this->container;
    }

    private function detectKernel(): string
    {
        return match (php_sapi_name()) {
            'cli', 'phpdbg' => 'kernel.cli',
            default => 'kernel.http',
        };
    }

    /**
     * @throws ReflectionException
     * @throws ContainerException
     */
    private function compileIfConfigured(): void
    {
        if (
            $this->container === null ||
            $this->compiledFilePath === null ||
            $this->compiledClass === null
        ) {
            return;
        }

        $compiler = new ContainerCompiler($this->container);
        $compiler->compile(
            $this->compiledFilePath,
            $this->compiledClass,
            $this->compiledNamespace ?? ''
        );
    }

    public function compile(string $filePath, string $className, string $namespace = ''): self
    {
        $this->compiledFilePath = $filePath;
        $this->compiledClass = $className;
        $this->compiledNamespace = $namespace;
        return $this;
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

        /** It says "if !file_exists" right up there... */
        /** @psalm-suppress UnresolvableInclude */
        require_once $this->compiledFilePath;

        $fqcn = $this->compiledNamespace
            ? $this->compiledNamespace . '\\' . $this->compiledClass
            : $this->compiledClass;

        if (!class_exists($fqcn)) {
            throw new RuntimeException(
                "Compiled container class '$this->compiledClass' not found."
            );
        }

        /** It says "if !class_exists" right up there... */
        /** @psalm-suppress MixedMethodCall */
        $container = new $fqcn();
        if (!$container instanceof ArgonContainer) {
            throw new RuntimeException(
                "Compiled container must extend ArgonContainer."
            );
        }

        return $container;
    }

    private function setupErrorHandling(): void
    {
        set_error_handler(
        /**
         * @throws ErrorException
         */
            static function (
                int $severity,
                string $message,
                string $file,
                int $line
            ): bool {
                throw new ErrorException($message, 0, $severity, $file, $line);
            }
        );
    }

    /**
     * @param ArgonContainer $container
     * @return KernelInterface
     *
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function getKernel(ArgonContainer $container): KernelInterface
    {
        if (!$container->has(KernelInterface::class)) {
            throw new RuntimeException("No kernel registered. Expected binding for KernelInterface.");
        }

        $kernel = $container->get(KernelInterface::class);

        if (!$kernel instanceof KernelInterface) {
            throw new RuntimeException("Service bound to KernelInterface must implement KernelInterface.");
        }

        return $kernel;
    }
}
