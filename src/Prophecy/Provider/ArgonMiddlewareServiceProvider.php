<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Provider;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Contracts\Http\Server\Middleware\DispatcherInterface;
use Maduser\Argon\Contracts\Http\Server\Middleware\HtmlResponderInterface;
use Maduser\Argon\Contracts\Http\Server\Middleware\JsonResponderInterface;
use Maduser\Argon\Contracts\Http\Server\Middleware\PlainTextResponderInterface;
use Maduser\Argon\Contracts\Http\Server\Middleware\ResponseResponderInterface;
use Maduser\Argon\Http\Server\Middleware\Dispatcher;
use Maduser\Argon\Http\Server\Middleware\HtmlResponder;
use Maduser\Argon\Http\Server\Middleware\JsonResponder;
use Maduser\Argon\Http\Server\Middleware\PlainTextResponder;
use Maduser\Argon\Http\Server\Middleware\ResponseResponder;
use Maduser\Argon\Prophecy\Support\Tag;

class ArgonMiddlewareServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $container->set(DispatcherInterface::class, Dispatcher::class)
            ->tag([Tag::MIDDLEWARE_HTTP => ['priority' => 6000, 'group' => ['api', 'web']]]);

        $container->set(JsonResponderInterface::class, JsonResponder::class)
            ->tag([Tag::MIDDLEWARE_HTTP => ['priority' => 5800, 'group' => ['api', 'web']]]);

        $container->set(HtmlResponderInterface::class, HtmlResponder::class)
            ->tag([Tag::MIDDLEWARE_HTTP => ['priority' => 5600, 'group' => 'web']]);

        $container->set(PlainTextResponderInterface::class, PlainTextResponder::class)
            ->tag([Tag::MIDDLEWARE_HTTP => ['priority' => 5400, 'group' => 'web']]);

        $container->set(ResponseResponderInterface::class, ResponseResponder::class)
            ->tag([Tag::MIDDLEWARE_HTTP => ['priority' => 5200, 'group' => ['api', 'web']]]);
    }
}
