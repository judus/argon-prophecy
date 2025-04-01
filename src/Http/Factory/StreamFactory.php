<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Factory;

use Maduser\Argon\Http\Message\Stream;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

final class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'rb+');
        fwrite($resource, $content);
        rewind($resource);

        return new Stream($resource);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = fopen($filename, $mode);
        return new Stream($resource);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}
