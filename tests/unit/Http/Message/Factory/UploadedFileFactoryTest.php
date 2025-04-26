<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Message\Factory;

use Maduser\Argon\Http\Message\Factory\UploadedFileFactory;
use Maduser\Argon\Http\Message\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

final class UploadedFileFactoryTest extends TestCase
{
    private UploadedFileFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new UploadedFileFactory();
    }

    public function testCreateUploadedFileWithAllParameters(): void
    {
        $stream = new Stream('test content');

        $file = $this->factory->createUploadedFile(
            $stream,
            size: 11,
            error: \UPLOAD_ERR_OK,
            clientFilename: 'test.txt',
            clientMediaType: 'text/plain'
        );

        $this->assertSame(11, $file->getSize());
        $this->assertSame(\UPLOAD_ERR_OK, $file->getError());
        $this->assertSame('test.txt', $file->getClientFilename());
        $this->assertSame('text/plain', $file->getClientMediaType());
    }

    public function testCreateUploadedFileWithoutSizeUsesStreamSize(): void
    {
        $stream = new Stream('hello world');

        $file = $this->factory->createUploadedFile($stream);

        $this->assertSame($stream->getSize(), $file->getSize());
    }

    public function testCreateUploadedFileWithNegativeSizeThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Size must be non-negative.');

        $stream = new Stream('data');
        $this->factory->createUploadedFile($stream, size: -1);
    }

    public function testCreateUploadedFileWithoutOptionalParameters(): void
    {
        $stream = new Stream('foo bar');

        $file = $this->factory->createUploadedFile($stream);

        $this->assertNull($file->getClientFilename());
        $this->assertNull($file->getClientMediaType());
        $this->assertSame(\UPLOAD_ERR_OK, $file->getError());
    }
}
