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
                // Impossible to simulate fopen('php://temp', 'r+') failure without tampering with PHP internals.
                // This case is untestable under normal runtime conditions.
                // @codeCoverageIgnoreStart
                throw new RuntimeException('Failed to open temporary stream.');
                // @codeCoverageIgnoreEnd
            }

            fwrite($resource, $input);
            rewind($resource);
        } elseif (is_resource($input)) {
            $resource = $input;
        } else {
            throw new RuntimeException('Stream must be a string or resource.');
        }

        $this->resource = $resource;
        $this->updateSize();
    }

    public function __toString(): string
    {
        try {
            $this->rewind();
            return $this->getContents();
        } catch (Throwable $e) {
            // Silent fallback: __toString must never throw exceptions in PHP
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
            // ftell() failure would require invalid or corrupted resource.
            // Cannot trigger safely under normal testing.
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to get position.');
            // @codeCoverageIgnoreEnd
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
            // seek() nonzero return happens only on broken/corrupted streams, not reachable in clean unit tests.
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Seek failed.');
            // @codeCoverageIgnoreEnd
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
            // fwrite() returns false on OS-level I/O failure (e.g., disk full), cannot simulate reliably in unit tests.
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Write failed.');
            // @codeCoverageIgnoreEnd
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
            // fread() returns false on catastrophic stream failure, unlikely to simulate in unit tests.
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Read failed.');
            // @codeCoverageIgnoreEnd
        }

        return $data;
    }

    public function getContents(): string
    {
        $this->ensureValid();

        $contents = stream_get_contents($this->resource);
        if ($contents === false) {
            // stream_get_contents() failure only occurs with broken streams, can't simulate in unit tests.
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to read stream.');
            // @codeCoverageIgnoreEnd
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
