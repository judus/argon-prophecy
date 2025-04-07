<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\ServiceProviders;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Http\Factory\HttpPipelineFactory;
use Maduser\Argon\Http\Factory\ServerRequestFactory;
use Maduser\Argon\Http\Factory\StreamFactory;
use Maduser\Argon\Http\Factory\UploadedFileFactory;
use Maduser\Argon\Http\Factory\UriFactory;
use Maduser\Argon\Http\Message\Response;
use Maduser\Argon\Http\Message\ServerRequest;
use Maduser\Argon\Http\Message\Stream;
use Maduser\Argon\Http\Message\Uri;
use Maduser\Argon\Http\Middleware\DispatchMiddleware;
use Maduser\Argon\Http\Middleware\HtmlResponderMiddleware;
use Maduser\Argon\Http\Middleware\JsonResponderMiddleware;
use Maduser\Argon\Http\Middleware\MiddlewarePipeline;
use Maduser\Argon\Routing\Contracts\RouteMatcherInterface;
use Maduser\Argon\Routing\RouteMatcher;
use Maduser\Argon\Routing\RouteMiddlewarePipeline;
use Maduser\Argon\Routing\RouteMatcherMiddleware;
use Maduser\Argon\Kernel\Contracts\KernelInterface;
use Maduser\Argon\Kernel\Exception\ExceptionHandlerInterface;
use Maduser\Argon\Kernel\Exception\HttpExceptionHandler;
use Maduser\Argon\Kernel\HttpKernel;
use Maduser\Argon\Routing\ArgonRouter;
use Maduser\Argon\Routing\Contracts\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ArgonHttpFoundation extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        /**
         * PSR-15: Exception handler (custom)
         */
        $container->singleton(ExceptionHandlerInterface::class, HttpExceptionHandler::class)
            ->tag(['exception.handler']);

        /**
         * PSR-7: ServerRequest (from globals)
         */
        $container->singleton(ServerRequestInterface::class, ServerRequest::class)
            ->useFactory(ServerRequestFactory::class, 'fromGlobals')
            ->tag(['http', 'psr-7', 'server_request']);

        /**
         * PSR-7: Response
         */
        $container->singleton(ResponseInterface::class, Response::class)
            ->tag(['http', 'psr-7', 'response']);

        /**
         * PSR-17: StreamFactory + Stream
         */
        $container->singleton(StreamFactoryInterface::class, StreamFactory::class)
            ->tag(['http', 'psr-17', 'stream']);

        $container->singleton(StreamInterface::class, Stream::class)
            ->tag(['http', 'psr-17', 'stream']);

        /**
         * PSR-17: UriFactory + Uri
         */
        $container->singleton(UriFactoryInterface::class, UriFactory::class)
            ->useFactory(UriFactory::class, 'createUri')
            ->tag(['http', 'psr-17', 'uri']);

        $container->singleton(UriInterface::class, Uri::class)
            ->useFactory(UriFactory::class, 'createUri')
            ->tag(['http', 'psr-17', 'uri']);

        /**
         * PSR-17: UploadedFileFactory
         */
        $container->singleton(UploadedFileFactoryInterface::class, UploadedFileFactory::class)
            ->useFactory(UploadedFileFactory::class, 'createUploadedFile')
            ->tag(['http', 'psr-17', 'uploaded_file']);

        /**
         * PSR-15: RequestHandler (middleware pipeline)
         */
        $container->singleton(RequestHandlerInterface::class, MiddlewarePipeline::class)
            ->useFactory(HttpPipelineFactory::class, 'create')
            ->tag(['http', 'psr-15', 'request_handler']);

        /**
         * Internal: Kernel and routing
         */
        $container->singleton(KernelInterface::class, HttpKernel::class);

        $container->singleton(RouterInterface::class, ArgonRouter::class)
            ->tag(['routing.adapter']);

        /**
         * Internal: HTTP middleware
         */
        $container->singleton(RouteMatcherInterface::class, RouteMatcher::class);

        $container->singleton(RouteMatcherMiddleware::class)
            ->tag(['middleware.http']);

        $container->singleton(RouteMiddlewarePipeline::class)
            ->tag(['middleware.router']);

        $container->singleton(DispatchMiddleware::class)
            ->tag(['middleware.http']);

        $container->singleton(JsonResponderMiddleware::class)
            ->tag(['middleware.http']);

        $container->singleton(HtmlResponderMiddleware::class)
            ->tag(['middleware.http']);


    }
}
