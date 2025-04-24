<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Provider;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Http\Message\Factory\ResponseFactory;
use Maduser\Argon\Http\Message\Factory\ServerRequestFactory;
use Maduser\Argon\Http\Message\Factory\StreamFactory;
use Maduser\Argon\Http\Message\Factory\UploadedFileFactory;
use Maduser\Argon\Http\Message\Factory\UriFactory;
use Maduser\Argon\Http\Message\Response;
use Maduser\Argon\Http\Message\ServerRequest;
use Maduser\Argon\Http\Message\Stream;
use Maduser\Argon\Http\Message\UploadedFile;
use Maduser\Argon\Http\Message\Uri;
use Maduser\Argon\Prophecy\Support\Tag;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class ArgonMessageServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $container->set(ServerRequestFactoryInterface::class, ServerRequestFactory::class)
            ->tag([Tag::HTTP, Tag::PSR17_FACTORY]);

        $container->set(ServerRequestInterface::class, ServerRequest::class)
            ->factory(ServerRequestFactoryInterface::class)
            ->tag([Tag::HTTP, Tag::PSR7_MESSAGE])
            ->transient();

        $container->set(ResponseFactoryInterface::class, ResponseFactory::class)
            ->tag([Tag::HTTP, Tag::PSR17_FACTORY]);

        $container->set(ResponseInterface::class, Response::class)
            ->factory(ResponseFactoryInterface::class, 'createResponse')
            ->tag([Tag::HTTP, Tag::PSR7_MESSAGE])
            ->transient();

        $container->set(StreamFactoryInterface::class, StreamFactory::class)
            ->tag([Tag::HTTP, Tag::PSR17_FACTORY]);

        $container->set(StreamInterface::class, Stream::class)
            ->factory(StreamFactoryInterface::class, 'createStream')
            ->tag([Tag::HTTP, Tag::PSR7_MESSAGE])
            ->transient();

        $container->set(UriFactoryInterface::class, UriFactory::class)
            ->tag([Tag::HTTP, Tag::PSR17_FACTORY]);

        $container->set(UriInterface::class, Uri::class)
            ->factory(UriFactoryInterface::class, 'createUri')
            ->tag([Tag::HTTP, Tag::PSR7_MESSAGE])
            ->transient();

        $container->set(UploadedFileFactoryInterface::class, UploadedFileFactory::class)
            ->tag([Tag::HTTP, Tag::PSR17_FACTORY]);

        $container->set(UploadedFileInterface::class, UploadedFile::class)
            ->factory(UploadedFileFactoryInterface::class, 'createUploadedFile')
            ->tag([Tag::HTTP, Tag::PSR7_MESSAGE])
            ->transient();
    }
}
