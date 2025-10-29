<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy;

use Closure;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Contracts\Handler\AppHandlerInterface;
use Maduser\Argon\Contracts\Handler\HttpKernelInterface;
use Maduser\Argon\Prophecy\Application\AppHandlerResolver;
use Maduser\Argon\Prophecy\Application\ApplicationLogging;
use Maduser\Argon\Prophecy\Application\ContainerManager;
use Maduser\Argon\Prophecy\Application\ErrorHandlerManager;
use Maduser\Argon\Prophecy\Contracts\ApplicationInterface;
use Maduser\Argon\Prophecy\Contracts\ErrorHandling\BootstrapErrorHandlerInterface;
use Maduser\Argon\Prophecy\ErrorHandling\BootstrapErrorHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use ReflectionException;
use RuntimeException;
use Throwable;

final class Application implements ApplicationInterface
{
    use ApplicationLogging;

    protected ?ArgonContainer $container = null;
    protected ?LoggerInterface $logger = null;
    private ?AppHandlerInterface $handler = null;
    private BootstrapErrorHandlerInterface $bootstrapErrorHandler;
    private ContainerManager $containerManager;
    private ErrorHandlerManager $errorManager;
    private AppHandlerResolver $handlerResolver;

    public function __construct(
        ?ArgonContainer $container = null,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger;
        $this->bootstrapErrorHandler = new BootstrapErrorHandler($this->logger);
        $this->bootstrapErrorHandler->register();
        $this->containerManager = new ContainerManager($container);
        $this->containerManager->setCwd($this->getCwd());
        $this->errorManager = new ErrorHandlerManager($this->bootstrapErrorHandler, $this->logger);
        $this->handlerResolver = new AppHandlerResolver();
        $this->container = $container;
    }

    public function register(Closure $closure): self
    {
        $this->containerManager->setConfigurator($closure);
        return $this;
    }

    public function compile(string $filePath, string $className, string $namespace = ''): self
    {
        $this->containerManager->configureCompilation($filePath, $className, $namespace);
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
            try {
                $handler->handle($request);
            } catch (Throwable $throwable) {
                $response = $this->errorManager->handleHttpThrowable(
                    $throwable,
                    $request,
                    $this->container ?? $this->containerManager->getContainer()
                );

                if ($response === null) {
                    return;
                }

                $this->errorManager->emitResponse($handler, $response);
            }

            return;
        }

        try {
            $exitCode = $handler->run();
        } catch (Throwable $throwable) {
            $this->errorManager->handleCliThrowable($throwable);
            return;
        }

        $handler->terminate($exitCode);
    }

    /**
     * @throws ReflectionException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws Throwable
     */
    public function process(?ServerRequestInterface $request = null): ResponseInterface
    {
        $handler = $this->bootstrap();

        if (!$handler instanceof HttpKernelInterface) {
            throw new RuntimeException('Active handler does not support HTTP processing.');
        }

        try {
            return $handler->process($request);
        } catch (Throwable $throwable) {
            $response = $this->errorManager->handleHttpThrowable(
                $throwable,
                $request,
                $this->container ?? $this->containerManager->getContainer()
            );

            if ($response === null) {
                throw $throwable;
            }

            return $response;
        }
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

        $container = $this->containerManager->getContainer();
        $this->container = $container;

        if ($container->has(LoggerInterface::class)) {
            $this->logger = $container->get(LoggerInterface::class);
            $this->errorManager->setLogger($this->logger);
        }

        $this->errorManager->registerRuntimeHandlerIfAvailable($container);

        $this->logContainerLoadedEvent();
        $container->boot();
        $this->logContainerBootedEvent();

        $handler = $this->handlerResolver->resolve($container);
        $this->errorManager->configureBootstrapModeFor($handler);

        $this->logHandlerReadyEvent($handler);

        return $this->handler = $handler;
    }

    protected function getContainerInstance(): ?ArgonContainer
    {
        return $this->container;
    }

    private function getCwd(): string
    {
        $sapi = php_sapi_name();

        if ($sapi === 'cli' || $sapi === 'phpdbg') {
            $cwd = getcwd();

            if ($cwd === false) {
                throw new RuntimeException('Unable to determine working directory from current environment.');
            }

            return $cwd;
        }

        if (!isset($_SERVER['SCRIPT_FILENAME'])) {
            throw new RuntimeException('Unable to determine working directory; SCRIPT_FILENAME is not defined.');
        }

        return dirname($_SERVER['SCRIPT_FILENAME']);
    }
}
