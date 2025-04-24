<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Provider;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Contracts\Http\Exception\ExceptionFormatterInterface;
use Maduser\Argon\Contracts\Http\Exception\ExceptionHandlerInterface;
use Maduser\Argon\Exception\ExceptionDispatcher;
use Maduser\Argon\Http\Exception\ExceptionFormatter;
use Maduser\Argon\Http\Exception\ExceptionHandler;
use Maduser\Argon\Prophecy\Support\Tag;

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

        $container->set(ExceptionHandlerInterface::class, ExceptionHandler::class)
            ->tag([Tag::EXCEPTION_HANDLER]);

        $container->set(ExceptionDispatcher::class)
            ->tag([Tag::EXCEPTION_DISPATCHER]);
    }
}
