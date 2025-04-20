<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Message;

use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Throwable;

final class Stream implements StreamInterface
{
    /** @var resource|null */
    private mixed $resource;
    private ?int $size = null;

    /**
     * @param string|resource $input
     */
    public static function create(mixed $input = ''): self
    {
        return new self($input);
    }

    public static function fromString(string $string): self
    {
        return new self($string);
    }

    /**
     * @param string|resource $resource
     */
    public static function fromResource(mixed $resource): self
    {
        if (!is_resource($resource)) {
            throw new RuntimeException('Not a valid stream resource.');
        }
        return new self($resource);
    }

    public static function fromFile(string $filename, string $mode = 'r'): self
    {
        $handle = @fopen($filename, $mode);
        if ($handle === false) {
            throw new RuntimeException("Failed to open file: $filename");
        }

        return new self($handle);
    }

    public static function null(): self
    {
        return new self(fopen('php://memory', 'r+'));
    }

    /**
     * @param mixed|resource $input
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
        } elseif (is_resource($input)) {
            $resource = $input;
        } else {
            throw new RuntimeException('Stream must be a string or resource.');
        }

        $this->resource = $resource;
    }

    public function __toString(): string
    {
        try {
            $this->rewind();
            return $this->getContents();
        } catch (Throwable) {
            return '';
        }
    }

    public function close(): void
    {
        $resource = $this->resource;

        if (is_resource($resource)) {
            fclose($resource);
        }

        $this->resource = null;
        $this->size = null;
    }

    public function detach()
    {
        $res = $this->resource;
        $this->resource = null;
        $this->size = null;
        return $res;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function tell(): int
    {
        $this->ensureValid();
        $pos = ftell($this->resource);
        if ($pos === false) {
            throw new RuntimeException('Failed to get position.');
        }

        return $pos;
    }

    public function eof(): bool
    {
        $this->ensureValid();
        return feof($this->resource);
    }

    public function isSeekable(): bool
    {
        $this->ensureValid();
        return stream_get_meta_data($this->resource)['seekable'];
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        $this->ensureValid();

        if (!$this->isSeekable()) {
            throw new RuntimeException('Stream not seekable.');
        }

        if (fseek($this->resource, $offset, $whence) !== 0) {
            throw new RuntimeException('Seek failed.');
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        $this->ensureValid();
        $mode = stream_get_meta_data($this->resource)['mode'];
        return strpbrk($mode, 'waxc+') !== false;
    }

    public function write($string): int
    {
        $this->ensureValid();

        if (!$this->isWritable()) {
            throw new RuntimeException('Stream not writable.');
        }

        $written = fwrite($this->resource, $string);
        if ($written === false) {
            throw new RuntimeException('Write failed.');
        }

        $this->updateSize();
        return $written;
    }

    public function isReadable(): bool
    {
        $this->ensureValid();
        $mode = stream_get_meta_data($this->resource)['mode'];
        return strpbrk($mode, 'r+') !== false;
    }

    public function read($length): string
    {
        $this->ensureValid();

        if (!$this->isReadable()) {
            throw new RuntimeException('Stream not readable.');
        }

        $data = fread($this->resource, $length);
        if ($data === false) {
            throw new RuntimeException('Read failed.');
        }

        return $data;
    }

    public function getContents(): string
    {
        $this->ensureValid();

        $contents = stream_get_contents($this->resource);
        if ($contents === false) {
            throw new RuntimeException('Failed to read stream.');
        }

        return $contents;
    }

    public function getMetadata($key = null): mixed
    {
        $this->ensureValid();
        $meta = stream_get_meta_data($this->resource);
        return $key === null ? $meta : ($meta[$key] ?? null);
    }

    private function updateSize(): void
    {
        $this->ensureValid();
        $stats = fstat($this->resource);
        $this->size = $stats['size'] ?? null;
    }

    /**
     * @psalm-assert resource $this->resource
     */
    private function ensureValid(): void
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Stream is detached or closed.');
        }
    }
}
