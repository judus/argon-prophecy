<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Provider;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Exception\ExceptionDispatcher;
use Maduser\Argon\Http\Exception\ExceptionFormatter;
use Maduser\Argon\Contracts\Http\Exception\ExceptionFormatterInterface;
use Maduser\Argon\Http\Server\Middleware\PlainTextResponder;
use Maduser\Argon\Http\Message\UploadedFile;
use Maduser\Argon\Http\Server\Middleware\ResponseResponder;
use Maduser\Argon\Middleware\Contracts\MiddlewareLoaderInterface;
use Maduser\Argon\Middleware\Contracts\MiddlewarePipelineCacheInterface;
use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Contracts\PipelineStoreInterface;
use Maduser\Argon\Middleware\Factory\RequestHandlerFactory;
use Maduser\Argon\Middleware\Loader\TaggedMiddlewareLoader;
use Maduser\Argon\Middleware\MiddlewarePipelineCache;
use Maduser\Argon\Middleware\PipelineManager;
use Maduser\Argon\Middleware\Resolver\ContainerMiddlewareResolver;
use Maduser\Argon\Middleware\Store\ContainerStore as MiddlewareStoreAlias;
use Maduser\Argon\Routing\Contracts\RequestHandlerResolverInterface;
use Maduser\Argon\Routing\Contracts\RouteContextInterface;
use Maduser\Argon\Routing\Contracts\RouteStoreInterface;
use Maduser\Argon\Routing\Middleware\RouteDispatcherMiddleware;
use Maduser\Argon\Routing\RequestHandlerResolver;
use Maduser\Argon\Routing\RouteContext;
use Maduser\Argon\Routing\Store\ContainerStore as RouteStoreAlias;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Maduser\Argon\Http\Message\Response;
use Maduser\Argon\Http\Message\ServerRequest;
use Maduser\Argon\Http\Message\Stream;
use Maduser\Argon\Http\Message\Uri;
use Maduser\Argon\Http\Message\Factory\ResponseFactory;
use Maduser\Argon\Http\Message\Factory\ServerRequestFactory;
use Maduser\Argon\Http\Message\Factory\StreamFactory;
use Maduser\Argon\Http\Message\Factory\UriFactory;
use Maduser\Argon\Http\Message\Factory\UploadedFileFactory;
use Maduser\Argon\Http\Server\MiddlewarePipeline;
use Maduser\Argon\Http\Server\Middleware\JsonResponder;
use Maduser\Argon\View\Middleware\HtmlResponderMiddleware;
use Maduser\Argon\Routing\Middleware\RouteMatcherMiddleware;
use Maduser\Argon\Routing\Middleware\DispatchMiddleware;
use Maduser\Argon\Routing\Contracts\RouterInterface;
use Maduser\Argon\Routing\Contracts\RouteMatcherInterface;
use Maduser\Argon\Routing\ArgonRouter;
use Maduser\Argon\Routing\RouteMatcher;
use Maduser\Argon\Routing\RoutePipeline;
use Maduser\Argon\Contracts\KernelInterface;
use Maduser\Argon\Http\Kernel;
use Maduser\Argon\Contracts\Http\Exception\ExceptionHandlerInterface;
use Maduser\Argon\Http\Exception\ExceptionHandler;

final class ArgonHttpFoundation extends AbstractServiceProvider
{
    /**
     * PSR-15 Exception Handler
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $parameters = $container->getParameters();
        $parameters->set('debug', false);

        $container->set(ExceptionFormatterInterface::class, ExceptionFormatter::class, [
            'debug' => $parameters->get('debug')
        ])
            ->tag(['exception.formatter']);

        $container->set(ExceptionHandlerInterface::class, ExceptionHandler::class)
            ->tag(['exception.handler']);

        $container->set(ExceptionDispatcher::class)
            ->tag(['exception.dispatcher']);

        /**
         * PSR-17/7: HTTP Messages
         */
        $container->set(ServerRequestFactoryInterface::class, ServerRequestFactory::class)
            ->tag(['http', 'psr-17', 'factory']);

        $container->set(ServerRequestInterface::class, ServerRequest::class)
            ->factory(ServerRequestFactoryInterface::class)
            ->tag(['http', 'psr-7', 'message'])
            ->transient();

        $container->set(ResponseFactoryInterface::class, ResponseFactory::class)
            ->tag(['http', 'psr-17', 'factory']);

        $container->set(ResponseInterface::class, Response::class)
            ->factory(ResponseFactoryInterface::class, 'createResponse')
            ->tag(['http', 'psr-7', 'message'])
            ->transient();

        $container->set(StreamFactoryInterface::class, StreamFactory::class)
            ->tag(['http', 'psr-17', 'factory']);

        $container->set(StreamInterface::class, Stream::class)
            ->factory(StreamFactoryInterface::class, 'createStream')
            ->tag(['http', 'psr-7', 'message'])
            ->transient();

        $container->set(UriFactoryInterface::class, UriFactory::class)
            ->tag(['http', 'psr-17', 'factory']);

        $container->set(UriInterface::class, Uri::class)
            ->factory(UriFactoryInterface::class, 'createUri')
            ->tag(['http', 'psr-7', 'message'])
            ->transient();

        $container->set(UploadedFileFactoryInterface::class, UploadedFileFactory::class)
            ->tag(['http', 'psr-17', 'factory']);

        $container->set(UploadedFileInterface::class, UploadedFile::class)
            ->factory(UploadedFileFactoryInterface::class, 'createUploadedFile')
            ->tag(['http', 'psr-7', 'message'])
            ->transient();

        /**
         * PSR-15: Middleware Pipeline
         */
        $container->set(RequestHandlerFactory::class)
            ->tag(['middleware.factory']);

        $container->set(MiddlewareLoaderInterface::class, TaggedMiddlewareLoader::class)
            ->tag(['middleware.loader']);

        $container->set(MiddlewarePipelineCacheInterface::class, MiddlewarePipelineCache::class)
            ->tag(['middleware.cache']);

        $container->set(MiddlewareResolverInterface::class, ContainerMiddlewareResolver::class)
            ->tag(['middleware.resolver']);

        $container->set(PipelineStoreInterface::class, MiddlewareStoreAlias::class)
            ->tag(['middleware.store']);

        $container->set(PipelineManager::class);

        $container->set(RequestHandlerInterface::class, MiddlewarePipeline::class)
            ->factory(RequestHandlerFactory::class, 'createFromRouteContext')
            ->tag(['middleware.pipeline', 'psr-15']);

        /**
         * PSR-15: Routed Middleware Pipelines
         */
        $container->set(RouteStoreInterface::class, RouteStoreAlias::class);

        $container->set(RouteMatcherInterface::class, RouteMatcher::class)
            ->tag(['routing.matcher']);

        $container->set(RouteContextInterface::class, RouteContext::class)
            ->tag(['routing.context']);

        $container->set(RouterInterface::class, ArgonRouter::class, [
        ])->tag(['routing.adapter']);

        $container->set(RequestHandlerResolverInterface::class, RequestHandlerResolver::class);

        /**
         * Kernel and Routing
         */
        $container->set(KernelInterface::class, Kernel::class)
            ->tag(['kernel.http']);

        /**
         * HTTP Middleware
         */
        $container->set(RouteDispatcherMiddleware::class)
            ->tag(['middleware.http' => ['priority' => 3000, 'group' => ['api', 'web']]]);

        $container->set(JsonResponder::class)
            ->tag(['middleware.http' => ['priority' => 2300, 'group' => ['api', 'web']]]);

        $container->set(PlainTextResponder::class)
            ->tag(['middleware.http' => ['priority' => 2100, 'group' => 'web']]);

        $container->set(ResponseResponder::class)
            ->tag(['middleware.http' => ['priority' => 1100, 'group' => ['api', 'web']]]);
    }
}
