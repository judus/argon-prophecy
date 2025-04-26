<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Message\Factory;

use Maduser\Argon\Http\Message\Stream;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

final class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'rb+');
        if ($resource === false) {
            // Reason: Opening 'php://temp' is guaranteed to succeed unless PHP's internal memory stream handling
            // is corrupted, which is outside testable application behavior.
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to open temporary stream.');
            // @codeCoverageIgnoreEnd
        }

        fwrite($resource, $content);
        rewind($resource);

        return new Stream($resource);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = @fopen($filename, $mode);
        if ($resource === false) {
            throw new RuntimeException("Failed to open file: $filename");
        }

        return new Stream($resource);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (!is_resource($resource)) {
            throw new RuntimeException('Expected a valid resource.');
        }

        return new Stream($resource);
    }
}
