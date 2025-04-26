<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Message\Factory;

use Maduser\Argon\Http\Message\Factory\StreamFactory;
use Maduser\Argon\Http\Message\Stream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class StreamFactoryTest extends TestCase
{
    private StreamFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new StreamFactory();
    }

    public function testCreateStream(): void
    {
        $stream = $this->factory->createStream('foobar');

        self::assertSame('foobar', (string) $stream);
        self::assertTrue($stream->isReadable());
        self::assertTrue($stream->isWritable());
    }

    public function testCreateStreamFromFile(): void
    {
        $filename = tempnam(sys_get_temp_dir(), 'streamtest');
        file_put_contents($filename, 'file content');

        $stream = $this->factory->createStreamFromFile($filename);

        self::assertSame('file content', (string) $stream);
        self::assertTrue($stream->isReadable());

        unlink($filename);
    }

    public function testCreateStreamFromResource(): void
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'resource content');
        rewind($resource);

        $stream = $this->factory->createStreamFromResource($resource);

        self::assertSame('resource content', (string) $stream);
    }

    public function testCreateStreamFromResourceThrowsOnInvalidInput(): void
    {
        $this->expectException(RuntimeException::class);

        $this->factory->createStreamFromResource('not-a-resource');
    }

    public function testFromFileThrowsWhenFileDoesNotExist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to open file: /path/to/nonexistent.file');

        Stream::fromFile('/path/to/nonexistent.file', 'r');
    }

    public function testCreateStreamFromFileThrowsExceptionOnInvalidFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to open file: /path/to/nonexistent/file.txt');

        $factory = new StreamFactory();
        $factory->createStreamFromFile('/path/to/nonexistent/file.txt');
    }
}
