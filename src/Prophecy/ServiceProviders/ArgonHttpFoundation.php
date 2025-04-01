<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\ServiceProviders;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Contracts\ServiceProviderInterface;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Http\Factory\HttpMiddlewarePipelineFactory;
use Maduser\Argon\Http\Factory\ResponseFactory;
use Maduser\Argon\Http\Factory\ServerRequestFactory;
use Maduser\Argon\Http\Factory\StreamFactory;
use Maduser\Argon\Http\Factory\UploadedFileFactory;
use Maduser\Argon\Http\Factory\UriFactory;
use Maduser\Argon\Http\Message\Response;
use Maduser\Argon\Http\Message\ServerRequest;
use Maduser\Argon\Http\Message\Stream;
use Maduser\Argon\Http\Middleware\DispatchMiddleware;
use Maduser\Argon\Http\Middleware\FinalResponderMiddleware;
use Maduser\Argon\Http\Middleware\HtmlResponderMiddleware;
use Maduser\Argon\Http\Middleware\JsonResponderMiddleware;
use Maduser\Argon\Http\Middleware\MiddlewarePipeline;
use Maduser\Argon\Http\Middleware\RoutingMiddleware;
use Maduser\Argon\Kernel\Exception\ExceptionHandlerInterface;
use Maduser\Argon\Kernel\Exception\HttpExceptionHandler;
use Maduser\Argon\Kernel\HttpKernel;
use Maduser\Argon\Routing\Adapters\ArgonRouteAdapter;
use Maduser\Argon\Routing\Contracts\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ArgonHttpFoundation implements ServiceProviderInterface
{
    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $container->singleton(ExceptionHandlerInterface::class, HttpExceptionHandler::class)
            ->tag(['exception.handler']);

        // PSR-7: ServerRequest
        $container->singleton(ServerRequestInterface::class, ServerRequest::class)
            ->useFactory(ServerRequestFactory::class, 'fromGlobals')
            ->tag(['http', 'psr-7', 'server_request']);

        // PSR-7: Response
        $container->singleton(ResponseInterface::class, Response::class)
            ->tag(['http', 'psr-7', 'response']);

        // PSR-17: StreamFactory
        $container->singleton(StreamFactoryInterface::class, StreamFactory::class)
            ->tag(['http', 'psr-17', 'stream']);

        $container->singleton(StreamInterface::class, Stream::class)
            ->tag(['http', 'psr-17', 'stream']);

        // PSR-17: UriFactory
        $container->singleton(UriFactoryInterface::class, UriFactory::class)
            ->useFactory(UriFactory::class, 'createUri')
            ->tag(['http', 'psr-17', 'uri']);

        // PSR-17: UploadedFileFactory
        $container->singleton(UploadedFileFactoryInterface::class, UploadedFileFactory::class)
            ->useFactory(UploadedFileFactory::class, 'createUploadedFile')
            ->tag(['http', 'psr-17', 'uploaded_file']);

        $container->singleton(RequestHandlerInterface::class, MiddlewarePipeline::class)
            ->useFactory(HttpMiddlewarePipelineFactory::class, 'create')
            ->tag(['http', 'psr-15', 'request_handler']);

        // Kernel bindings
        $container->singleton(RouterInterface::class, ArgonRouteAdapter::class)
            ->tag(['routing.adapter']);

        $container->singleton(RoutingMiddleware::class)
            ->tag(['middleware.http']);

        $container->singleton(DispatchMiddleware::class)
            ->tag(['middleware.http']);

        $container->singleton(JsonResponderMiddleware::class)
            ->tag(['middleware.http']);

        $container->singleton(HtmlResponderMiddleware::class)
            ->tag(['middleware.http']);

        $container->singleton('kernel.http', HttpKernel::class)
            ->tag(['kernel.http']);
    }

    public function boot(ArgonContainer $container): void
    {
        // No-op. This provider has no boot-time logic.
    }
}
