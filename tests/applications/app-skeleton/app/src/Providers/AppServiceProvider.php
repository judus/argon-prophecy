<?php

declare(strict_types=1);

namespace App\Providers;

use App\Console\GreetCommand;
use App\Console\SymfonyCommand;
use App\Exceptions\WhoopsExceptionHandler;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestClass;
use App\Http\Controllers\TestClassInterface;
use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Contracts\ParameterStoreInterface;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Exception\ExceptionDispatcher;
use Maduser\Argon\Contracts\Http\Exception\ExceptionHandlerInterface;
use Maduser\Argon\Http\Server\Middleware\JsonResponder;
use Maduser\Argon\Http\Server\Middleware\PlainTextResponder;
use Maduser\Argon\Routing\Contracts\RouterInterface;
use Maduser\Argon\View\Provider\ViewServiceProvider;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class AppServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function register(ArgonContainer $container): void
    {
        $this->configureParameters($container);

        $parameters = $container->getParameters();

        if ($parameters->get('debug')) {
            $container->set(ExceptionHandlerInterface::class, WhoopsExceptionHandler::class);
        }

        $container->set(HomeController::class);
        $container->set(TestClassInterface::class, TestClass::class);

        $container->register(EloquentServiceProvider::class);
        $container->register(ViewServiceProvider::class);

        $this->registerRoutes($container);
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
    private function registerRoutes(ArgonContainer $container): void
    {
        $router = $container->get(RouterInterface::class);

        $router->get('/', [HomeController::class, 'index'], ['api']);

        $router->group(['web'], '/demo', function (RouterInterface $router) {
            $router->get('/params/{id}/{cat}', [HomeController::class, 'onlyParams'], [JsonResponder::class]);
            $router->get('/injected', [HomeController::class, 'injectedDependency']);
            $router->get('/injected/{id}', [HomeController::class, 'injectedAndParams']);
            $router->get('/error', [HomeController::class, 'throws']);
            $router->get('/plain', [HomeController::class, 'stringResponse']);
            $router->get('/response/object', [HomeController::class, 'responseObject']);
            $router->get('/twig', [HomeController::class, 'twigResponse']);
        });
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
                $logger->critical('ALEEEERT!!', [
                    'exception' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'uri' => $request->getUri()->getPath(),
                ]);
            }
        );
    }
}
