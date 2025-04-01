<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Contracts;

use Maduser\Argon\Http\ResponseFactory;
use Maduser\Argon\Http\ServerRequestFactory;
use Maduser\Argon\Http\StreamFactory;
use Maduser\Argon\Http\UploadedFileFactory;
use Maduser\Argon\Http\UriFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

final class Psr17FactoryAggregate implements Psr17FactoryAggregateInterface
{
    public function responseFactory(): ResponseFactoryInterface
    {
        return new ResponseFactory();
    }

    public function requestFactory(): ServerRequestFactoryInterface
    {
        return new ServerRequestFactory();
    }

    public function streamFactory(): StreamFactoryInterface
    {
        return new StreamFactory();
    }

    public function uploadedFileFactory(): UploadedFileFactoryInterface
    {
        return new UploadedFileFactory();
    }

    public function uriFactory(): UriFactoryInterface
    {
        return new UriFactory();
    }
}
