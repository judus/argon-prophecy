<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Application;

use Closure;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Compiler\ContainerCompiler;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Prophecy\Exceptions\ProphecyException;
use ReflectionException;

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
    private ?string $cwd = null;

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

    public function setCwd(string $cwd): void
    {
        $this->cwd = $cwd;
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
            throw ProphecyException::compiledContainerClassNotFound($this->compiledClass);
        }

        /** @psalm-suppress MixedMethodCall */
        $container = new $fqcn();
        if (!$container instanceof ArgonContainer) {
            throw ProphecyException::compiledContainerMustExtendArgonContainer();
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
        $parameters = $container->getParameters();
        $cwd = $this->resolveCwd();

        $parameters->set('cwd', $cwd);

        if ($this->configurator !== null) {
            ($this->configurator)($container);
        }

        if ($parameters->get('cwd') !== $cwd) {
            throw ProphecyException::cwdMutationAttempted();
        }

        if (!$parameters->has('basePath')) {
            $parameters->set('basePath', $cwd);
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

    private function resolveCwd(): string
    {
        if ($this->cwd === null) {
            throw ProphecyException::cwdMissing();
        }

        return $this->cwd;
    }
}
