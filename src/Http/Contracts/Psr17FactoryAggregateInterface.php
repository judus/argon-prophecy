<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Contracts;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

interface Psr17FactoryAggregateInterface
{
    public function responseFactory(): ResponseFactoryInterface;

    public function requestFactory(): ServerRequestFactoryInterface;

    public function streamFactory(): StreamFactoryInterface;

    public function uploadedFileFactory(): UploadedFileFactoryInterface;

    public function uriFactory(): UriFactoryInterface;
}
