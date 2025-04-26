<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Provider;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionDispatcherInterface;
use Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionFormatterInterface;
use Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionHandlerInterface;
use Maduser\Argon\ErrorHandling\Http\ExceptionDispatcher;
use Maduser\Argon\ErrorHandling\Http\ExceptionFormatter;
use Maduser\Argon\ErrorHandling\Http\ExceptionHandler;
use Maduser\Argon\Prophecy\Support\Tag;
use Psr\Log\LoggerInterface;

class ArgonExceptionServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $debug = ['debug' => $container->getParameters()->get('debug', false)];

        $container->set(ExceptionFormatterInterface::class, ExceptionFormatter::class, $debug)
            ->tag([Tag::EXCEPTION_FORMATTER]);

        $container->set(ExceptionHandlerInterface::class, ExceptionHandler::class, [
            'logger' => LoggerInterface::class,
        ])
            ->tag([Tag::EXCEPTION_HANDLER]);

        $container->set(ExceptionDispatcherInterface::class, ExceptionDispatcher::class)
            ->tag([Tag::EXCEPTION_DISPATCHER]);
    }
}
