<?php

declare(strict_types=1);

namespace App\Providers;

use App\Console\GreetCommand;
use App\Console\PingCommand;
use App\Console\SymfonyCommand;
use App\Exception\WhoopsExceptionHandler;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestClass;
use App\Http\Controllers\TestClassInterface;
use App\Middlewares\DumbMiddleware;
use Maduser\Argon\Console\ArgonConsoleInput;
use Maduser\Argon\Console\Contracts\ConsoleInterface;
use Maduser\Argon\Console\SymfonyConsoleAdapter;
use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Contracts\ServiceProviderInterface;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Kernel\Exception\ExceptionHandlerInterface;
use Maduser\Argon\Routing\Contracts\RouterInterface;
use Psr\Container\ContainerInterface;

class AppServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function register(ArgonContainer $container): void
    {
        $container->singleton(ExceptionHandlerInterface::class, WhoopsExceptionHandler::class)
            ->tag(['exception.handler']);

        $container->singleton(ConsoleInterface::class, SymfonyConsoleAdapter::class);

//        $container->singleton(DumbMiddleware::class)
//            ->tag(['middleware.route']);

        $container->singleton(SymfonyCommand::class)
            ->tag(['cli.command']);

        $container->singleton(GreetCommand::class)
            ->tag(['cli.command']);

        $container->register(EloquentServiceProvider::class);

        $container->singleton(TestClassInterface::class, TestClass::class);

        $container->singleton(HomeController::class);

        $this->registerRoutes($container);

    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function registerRoutes(ArgonContainer $container): void
    {
        $router = $container->get(RouterInterface::class);

        $router->get('/', [HomeController::class, 'index']);

        $router->group([], '/demo', function (RouterInterface $router) {
            $router->get('/params/{id}/{cat}', [HomeController::class, 'onlyParams']);
            $router->get('/injected', [HomeController::class, 'injectedDependency']);
            $router->get('/injected/{id}', [HomeController::class, 'injectedAndParams']);
            $router->get('/error', [HomeController::class, 'throws']);
            $router->get('/plain', [HomeController::class, 'stringResponse']);
        });
    }
}
