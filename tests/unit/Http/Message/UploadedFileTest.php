<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Message;

use InvalidArgumentException;
use Maduser\Argon\Http\Message\UploadedFile;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use ReflectionClass;
use RuntimeException;

final class UploadedFileTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetters(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        $file = new UploadedFile($stream, 1234, UPLOAD_ERR_OK, 'file.txt', 'text/plain');

        $this->assertSame($stream, $file->getStream());
        $this->assertSame(1234, $file->getSize());
        $this->assertSame(UPLOAD_ERR_OK, $file->getError());
        $this->assertSame('file.txt', $file->getClientFilename());
        $this->assertSame('text/plain', $file->getClientMediaType());
    }

    /**
     * @throws Exception
     */
    public function testGetStreamAfterMoveThrowsException(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        $file = new UploadedFile($stream, 1234);

        // Mark as moved manually for test
        $reflection = new ReflectionClass($file);
        $property = $reflection->getProperty('moved');
        $property->setValue($file, true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot retrieve stream after file has been moved.');
        $file->getStream();
    }

    /**
     * @throws Exception
     */
    public function testMoveToFailsIfAlreadyMoved(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        $file = new UploadedFile($stream, 1234);

        $reflection = new ReflectionClass($file);
        $property = $reflection->getProperty('moved');
        $property->setValue($file, true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File has already been moved.');
        $file->moveTo('/tmp/somewhere.txt');
    }

    /**
     * @throws Exception
     */
    public function testMoveToFailsWithEmptyTargetPath(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        $file = new UploadedFile($stream, 1234);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Target path must not be empty.');
        $file->moveTo('');
    }

    /**
     * @throws Exception
     */
    public function testMoveToFailsIfInvalidDirectory(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        $file = new UploadedFile($stream, 1234);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid target directory: /some/fake/path/file.txt');
        $file->moveTo('/some/fake/path/file.txt');
    }

    /**
     * @throws Exception
     */
    public function testMoveToFailsIfNotWritable(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        $file = new UploadedFile($stream, 1234);

        $dir = sys_get_temp_dir() . '/not_writable';
        mkdir($dir, 0444); // read-only directory

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessageMatches('/Target directory is not writable/');

            $file->moveTo($dir . '/test.txt');
        } finally {
            chmod($dir, 0777);
            rmdir($dir);
        }
    }

    /**
     * @throws Exception
     */
    public function testMoveToSuccess(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('eof')->willReturnOnConsecutiveCalls(false, true);
        $stream->method('read')->willReturn('data');
        $stream->expects($this->once())->method('rewind');

        $file = new UploadedFile($stream, 4);

        $tmpFile = tempnam(sys_get_temp_dir(), 'upl');

        $file->moveTo($tmpFile);

        $this->assertFileExists($tmpFile);
        $this->assertSame('data', file_get_contents($tmpFile));

        unlink($tmpFile);
    }
}
