<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Application;

use Closure;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Compiler\ContainerCompiler;
use Maduser\Argon\Container\Exceptions\ContainerException;
use ReflectionException;
use RuntimeException;

/**
 * Internal helper that encapsulates container loading, compilation, and caching.
 *
 * @internal
 */
final class ContainerManager
{
    private ?ArgonContainer $container;
    private ?Closure $configurator = null;
    private ?string $compiledFilePath = null;
    private ?string $compiledClass = null;
    private ?string $compiledNamespace = null;
    private ?string $basePath = null;

    public function __construct(?ArgonContainer $container = null)
    {
        $this->container = $container;
    }

    public function setConfigurator(?Closure $configurator): void
    {
        $this->configurator = $configurator;
    }

    public function configureCompilation(string $filePath, string $className, string $namespace = ''): void
    {
        $this->compiledFilePath = $filePath;
        $this->compiledClass = $className;
        $this->compiledNamespace = $namespace !== '' ? $namespace : null;
    }

    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }

    /**
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function getContainer(): ArgonContainer
    {
        if ($this->container instanceof ArgonContainer) {
            return $this->container;
        }

        if ($compiled = $this->loadCompiledContainer()) {
            return $this->container = $compiled;
        }

        return $this->container = $this->buildContainer();
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

        /** @psalm-suppress UnresolvableInclude */
        require_once $this->compiledFilePath;

        $fqcn = $this->compiledNamespace !== null
            ? $this->compiledNamespace . '\\' . $this->compiledClass
            : $this->compiledClass;

        if (!class_exists($fqcn)) {
            throw new RuntimeException("Compiled container class '{$this->compiledClass}' not found.");
        }

        /** @psalm-suppress MixedMethodCall */
        $container = new $fqcn();
        if (!$container instanceof ArgonContainer) {
            throw new RuntimeException('Compiled container must extend ArgonContainer.');
        }

        return $container;
    }

    /**
     * @throws ContainerException
     * @throws ReflectionException
     */
    private function buildContainer(): ArgonContainer
    {
        $container = new ArgonContainer();

        $basePath = $this->basePath
            ?? dirname($_SERVER['SCRIPT_FILENAME'] ?? __DIR__, 4);

        $container->getParameters()->set('basePath', $basePath);

        if ($this->configurator !== null) {
            ($this->configurator)($container);
        }

        $this->compileIfConfigured($container);

        return $container;
    }

    /**
     * @throws ReflectionException
     * @throws ContainerException
     */
    private function compileIfConfigured(ArgonContainer $container): void
    {
        if ($this->compiledFilePath === null || $this->compiledClass === null) {
            return;
        }

        $compiler = new ContainerCompiler($container);
        $compiler->compile(
            $this->compiledFilePath,
            $this->compiledClass,
            $this->compiledNamespace ?? ''
        );
    }
}
