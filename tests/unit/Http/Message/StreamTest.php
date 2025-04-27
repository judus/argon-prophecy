<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Message;

use Maduser\Argon\Http\Message\Stream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class StreamTest extends TestCase
{
    public function testConstructorThrowsIfInvalidInput(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream must be a string or resource.');

        new Stream(123); // invalid input
    }

    public function testCreateFromString(): void
    {
        $stream = Stream::create('Hello World');
        $this->assertSame('Hello World', (string) $stream);
    }

    public function testWriteAndRead(): void
    {
        $stream = Stream::create();
        $stream->write('TestData');

        $stream->rewind();
        $this->assertSame('TestData', $stream->read(8));
    }

    public function testSeekAndTell(): void
    {
        $stream = Stream::create('1234567890');
        $stream->seek(5);
        $this->assertSame(5, $stream->tell());
    }

    public function testIsSeekableReadableWritable(): void
    {
        $stream = Stream::create('test');
        $this->assertTrue($stream->isSeekable());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
    }

    public function testGetMetadata(): void
    {
        $stream = Stream::create('data');
        $meta = $stream->getMetadata();
        $this->assertArrayHasKey('mode', $meta);
        $this->assertTrue($meta['seekable']);
    }

    public function testDetachInvalidatesStream(): void
    {
        $stream = Stream::create('something');
        $stream->detach();

        $this->expectException(RuntimeException::class);
        $stream->tell();
    }

    public function testCloseInvalidatesStream(): void
    {
        $stream = Stream::create('closing');
        $stream->close();

        $this->expectException(RuntimeException::class);
        $stream->read(1);
    }

    public function testEof(): void
    {
        $stream = Stream::create('abc');
        $stream->read(3);

        $stream->read(1);

        $this->assertTrue($stream->eof());
    }

    public function testFromFile(): void
    {
        $tmp = tmpfile();
        fwrite($tmp, 'filedata');
        rewind($tmp);

        $stream = Stream::fromResource($tmp);
        $this->assertSame('filedata', $stream->getContents());

        fclose($tmp);
    }

    public function testFromFileCreatesStream(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'StreamTest');
        assert($tmpFile !== false);

        file_put_contents($tmpFile, 'test content');

        $stream = Stream::fromFile($tmpFile);

        $this->assertInstanceOf(Stream::class, $stream);

        unlink($tmpFile);
    }

    public function testFromInvalidResourceThrows(): void
    {
        $this->expectException(RuntimeException::class);
        Stream::fromResource('invalid');
    }

    public function testFromStringCreatesStream(): void
    {
        $stream = Stream::fromString('hello');
        $this->assertSame('hello', (string) $stream);
    }

    public function testNullCreatesEmptyStream(): void
    {
        $stream = Stream::null();
        $this->assertSame('', (string) $stream);
    }

    public function testFromNonExistingFileThrows(): void
    {
        $this->expectException(RuntimeException::class);
        Stream::fromFile('/path/to/missing/file.txt');
    }

    public function testWriteOnNonWritableStreamThrows(): void
    {
        $resource = fopen('php://memory', 'r'); // read-only
        $stream = Stream::fromResource($resource);

        $this->expectException(RuntimeException::class);
        $stream->write('data');
    }

    public function testReadOnNonReadableStreamThrows(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'StreamTest');
        assert($tmpFile !== false);

        $resource = fopen($tmpFile, 'w'); // TRUE write-only
        $stream = Stream::fromResource($resource);

        unlink($tmpFile); // Clean up immediately, resource still valid

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream not readable.');

        $stream->read(1);
    }

    public function testSeekOnNonSeekableStreamThrows(): void
    {
        $resource = fopen('php://output', 'w'); // not seekable
        $stream = Stream::fromResource($resource);

        $this->expectException(RuntimeException::class);
        $stream->seek(0);
    }

    public function testToStringOnClosedStreamReturnsEmptyString(): void
    {
        $stream = Stream::create('data');
        $stream->close();

        $this->assertSame('', (string) $stream);
    }
}
