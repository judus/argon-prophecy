<?php

declare(strict_types=1);

namespace Maduser\Argon\Logging;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LoggerServiceProvider extends AbstractServiceProvider
{
    /**
     * @param string $loggerClass
     * @param string $handlerClass
     */
    public function __construct(
        private readonly string $loggerClass = "\Monolog\Logger",
        private string $handlerClass = "\Monolog\Handler\StreamHandler"
    ) {
    }

    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $parameters = $container->getParameters();

        if (class_exists($this->loggerClass) && class_exists($this->handlerClass)) {
            $container->set(LoggerFactory::class, args: [
                'logLevel' => $parameters->get('logLevel', 200),
                'logFile' => $parameters->get('logFile', null),
            ]);

            $container->set(LoggerInterface::class, Logger::class)
                ->factory(LoggerFactory::class, 'create')
                ->tag('logger');
        }
    }
}
