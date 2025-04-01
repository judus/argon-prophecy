<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Message;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

final class Stream implements StreamInterface
{
    /**
     * @var closed-resource|null|resource
     */
    private $resource;
    private int $size;

    /**
     * @param mixed $input
     */
    public function __construct(mixed $input = '')
    {
        if (is_string($input)) {
            $resource = fopen('php://temp', 'r+');
            if ($resource === false) {
                throw new RuntimeException('Failed to open temporary stream.');
            }

            fwrite($resource, $input);
            rewind($resource);

            $this->resource = $resource;
        } elseif (is_resource($input)) {
            $this->resource = $input;
        } else {
            throw new RuntimeException('Stream must be a string or resource.');
        }

        $this->size = fstat($this->resource)['size'] ?? 0;
    }

    public function __toString(): string
    {
        try {
            $this->rewind();
            return $this->getContents();
        } catch (\Throwable) {
            return '';
        }
    }

    public function close(): void
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
    }

    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function tell(): int
    {
        $result = ftell($this->resource);
        if ($result === false) {
            throw new RuntimeException('Unable to determine stream position.');
        }
        return $result;
    }

    public function eof(): bool
    {
        return feof($this->resource);
    }

    public function isSeekable(): bool
    {
        $meta = stream_get_meta_data($this->resource);
        return $meta['seekable'];
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!$this->isSeekable()) {
            throw new RuntimeException('Stream is not seekable.');
        }

        if (fseek($this->resource, $offset, $whence) !== 0) {
            throw new RuntimeException('Failed to seek in stream.');
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        $mode = stream_get_meta_data($this->resource)['mode'];
        return str_contains($mode, 'w') || str_contains($mode, '+');
    }

    public function write($string): int
    {
        if (!$this->isWritable()) {
            throw new RuntimeException('Stream is not writable.');
        }

        $written = fwrite($this->resource, $string);
        if ($written === false) {
            throw new RuntimeException('Failed to write to stream.');
        }

        return $written;
    }

    public function isReadable(): bool
    {
        $mode = stream_get_meta_data($this->resource)['mode'];
        return str_contains($mode, 'r') || str_contains($mode, '+');
    }

    public function read($length): string
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable.');
        }

        $data = fread($this->resource, $length);
        if ($data === false) {
            throw new RuntimeException('Failed to read from stream.');
        }

        return $data;
    }

    public function getContents(): string
    {
        $contents = stream_get_contents($this->resource);
        if ($contents === false) {
            throw new RuntimeException('Unable to read stream contents.');
        }

        return $contents;
    }

    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->resource);
        return $key === null ? $meta : ($meta[$key] ?? null);
    }
}
