<?php

declare(strict_types=1);

namespace Maduser\Argon\Logging;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Psr\Log\LoggerInterface;

/**
 * @api
 */
final class LoggerServiceProvider extends AbstractServiceProvider
{
    /**
     * @param string $loggerClass
     * @param string $handlerClass
     */
    public function __construct(
        private readonly string $loggerClass = "\Monolog\Logger",
        private readonly string $handlerClass = "\Monolog\Handler\StreamHandler"
    ) {
    }

    /**
     * @throws ContainerException
     */
    #[\Override]
    public function register(ArgonContainer $container): void
    {
        $parameters = $container->getParameters();

        $container->set(LoggerFactory::class, args: [
            'logLevel' => $parameters->get('logLevel', 200),
            'logFile' => $parameters->get('logFile', null),
            'loggerClass' => $this->loggerClass,
            'handlerClass' => $this->handlerClass,
        ]);

        $container->set(LoggerInterface::class, LoggerFactory::class)
            ->factory(LoggerFactory::class, 'create')
            ->tag('logger');
    }
}
