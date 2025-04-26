<?php

declare(strict_types=1);

namespace Tests\Application\Mocks\Providers;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\ErrorHandling\Http\ExceptionDispatcher;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class UserExtendsDefaultStack extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function register(ArgonContainer $container): void
    {
        /** Configuration */
        $this->configureParameters($container);

        /** Exceptions handlers */
        $this->registerExceptionHandling($container);
    }

    /**
     * @param ArgonContainer $container
     */
    public function configureParameters(ArgonContainer $container): void
    {
        $parameters = $container->getParameters();

        $debug = filter_var(
            $_ENV['APP_DEBUG'] ?? false,
            FILTER_VALIDATE_BOOL
        );

        $parameters->set('debug', $debug);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function registerExceptionHandling(ArgonContainer $container): void
    {
        $dispatcher = $container->get(ExceptionDispatcher::class);
        $logger = $container->get(LoggerInterface::class);

        $dispatcher->register(
            RuntimeException::class,
            function (Throwable $exception, ServerRequestInterface $request) use ($logger) {
                $logger->critical('ALERT!!', [
                    'exception' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'uri' => $request->getUri()->getPath(),
                ]);
            }
        );
    }
}
