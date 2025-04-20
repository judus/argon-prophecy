<?php

declare(strict_types=1);

namespace Maduser\Argon\Logging;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

class LoggerServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $parameters = $container->getParameters();

        if (class_exists('Monolog\Logger') && class_exists('Monolog\Handler\StreamHandler')) {
            $container->set(LoggerFactory::class, args: [
                'logLevel' => $parameters->get('logLevel', 200),
                'logFile' => $parameters->get('logFile', null),
            ]);

            $container->set(LoggerInterface::class, Logger::class)
                ->factory(LoggerFactory::class, 'create')
                ->tag('logger');
        } else {
            $container->set(LoggerInterface::class, NullLogger::class);
        }
    }
}
