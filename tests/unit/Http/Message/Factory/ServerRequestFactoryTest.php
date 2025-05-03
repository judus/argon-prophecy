<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Message\Factory;

use Maduser\Argon\Http\Message\Factory\ServerRequestFactory;
use Maduser\Argon\Http\Message\ServerRequest;
use Maduser\Argon\Http\Message\UploadedFile;
use Maduser\Argon\Http\Message\Uri;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;
use ReflectionException;

final class ServerRequestFactoryTest extends TestCase
{
    public function testCreateServerRequest(): void
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('POST', 'https://example.com/foo');

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://example.com/foo', (string) $request->getUri());
    }

    public function testFromGlobalsDefaults(): void
    {
        $_SERVER = [];

        $request = ServerRequestFactory::fromGlobals();

        $this->assertInstanceOf(ServerRequest::class, $request);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('http://localhost/', (string) $request->getUri());
        $this->assertSame('1.1', $request->getProtocolVersion());
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateUriFromGlobals(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/test/123';

        $uri = $this->invokePrivateMethod(ServerRequestFactory::class, 'createUriFromGlobals');

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertSame('https://example.com/test/123', (string) $uri);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetProtocolVersion(): void
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/2.0';

        $version = $this->invokePrivateMethod(ServerRequestFactory::class, 'getProtocolVersion');

        $this->assertSame('2.0', $version);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetAllHeadersWithoutGetallheaders(): void
    {
        // Simulate missing getallheaders() by clearing it from functions
        // Not really doable. Instead: Test fallback directly:
        $_SERVER['HTTP_X_CUSTOM_HEADER'] = 'foobar';
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        $headers = $this->invokePrivateMethod(ServerRequestFactory::class, 'getAllHeaders');

        $this->assertArrayHasKey('x-custom-header', $headers);
        $this->assertArrayHasKey('content-type', $headers);
        $this->assertSame(['foobar'], $headers['x-custom-header']);
        $this->assertSame(['application/json'], $headers['content-type']);
    }

    /**
     * @throws ReflectionException
     */
    public function testNormalizeUploadedFilesSingle(): void
    {
        $mockUpload = [
            'tmp_name' => tempnam(sys_get_temp_dir(), 'upload_test'),
            'size' => 123,
            'error' => UPLOAD_ERR_OK,
            'name' => 'file.txt',
            'type' => 'text/plain',
        ];

        $normalized = $this->invokePrivateMethod(ServerRequestFactory::class, 'normalizeUploadedFiles', [
            ['file' => $mockUpload]
        ]);

        $this->assertInstanceOf(UploadedFileInterface::class, $normalized['file']);
    }

    /**
     * Helper to invoke private static methods for coverage
     * @param class-string $class
     * @throws ReflectionException
     */
    private function invokePrivateMethod(string $class, string $method, array $args = []): mixed
    {
        $reflection = new ReflectionClass($class);
        $refMethod = $reflection->getMethod($method);

        return $refMethod->invokeArgs(null, $args);
    }

    public function testParseServerHeadersParsesHttpHeadersCorrectly(): void
    {
        $server = [
            'HTTP_HOST' => 'example.com',
            'HTTP_USER_AGENT' => 'MadBrowser/1.0',
            'CONTENT_TYPE' => 'application/json',
            'CONTENT_LENGTH' => '123',
        ];

        $headers = ServerRequestFactory::parseServerHeaders($server);

        $this->assertSame([
            'host' => ['example.com'],
            'user-agent' => ['MadBrowser/1.0'],
            'content-type' => ['application/json'],
            'content-length' => ['123'],
        ], $headers);
    }

    public function testParseServerHeadersIgnoresNonHeaderEntries(): void
    {
        $server = [
            'SOME_RANDOM_KEY' => 'foobar',
            'ANOTHER_ONE' => 'baz',
        ];

        $headers = ServerRequestFactory::parseServerHeaders($server);

        $this->assertSame([], $headers);
    }

    /**
     * @throws Exception
     */
    public function testNormalizeUploadedFilesWithAlreadyNormalizedUploadedFile(): void
    {
        $mockUploadedFile = $this->createMock(UploadedFileInterface::class);

        $files = [
            'foo' => $mockUploadedFile,
        ];

        $normalized = self::invokeNormalizeUploadedFiles($files);

        $this->assertArrayHasKey('foo', $normalized);
        $this->assertSame($mockUploadedFile, $normalized['foo']);
    }

    /**
     * @throws Exception
     */
    public function testNormalizeUploadedFilesWithSingleUpload(): void
    {
        $tempFile = tmpfile();
        $metaData = stream_get_meta_data($tempFile);
        $tempFilePath = $metaData['uri'];

        $files = [
            'file' => [
                'tmp_name' => $tempFilePath,
                'name' => 'example.txt',
                'type' => 'text/plain',
                'size' => 123,
                'error' => 0,
            ],
        ];

        $normalized = self::invokeNormalizeUploadedFiles($files);

        $this->assertArrayHasKey('file', $normalized);
        $this->assertInstanceOf(UploadedFile::class, $normalized['file']);
        $this->assertSame(123, $normalized['file']->getSize());

        fclose($tempFile);
    }

    public function testNormalizeUploadedFilesWithMultipleUploads(): void
    {
        $tempFile1 = tmpfile();
        $metaData1 = stream_get_meta_data($tempFile1);
        $tempFilePath1 = $metaData1['uri'];

        $tempFile2 = tmpfile();
        $metaData2 = stream_get_meta_data($tempFile2);
        $tempFilePath2 = $metaData2['uri'];

        $files = [
            'files' => [
                'name' => ['example1.txt', 'example2.txt'],
                'type' => ['text/plain', 'text/plain'],
                'tmp_name' => [$tempFilePath1, $tempFilePath2],
                'error' => [0, 0],
                'size' => [123, 456],
            ],
        ];

        $normalized = self::invokeNormalizeUploadedFiles($files);

        $this->assertArrayHasKey('files', $normalized);
        $this->assertIsArray($normalized['files']);
        $this->assertInstanceOf(UploadedFile::class, $normalized['files'][0]);
        $this->assertInstanceOf(UploadedFile::class, $normalized['files'][1]);
        $this->assertSame(123, $normalized['files'][0]->getSize());
        $this->assertSame(456, $normalized['files'][1]->getSize());

        fclose($tempFile1);
        fclose($tempFile2);
    }

    /**
     * Helper to call private method normalizeUploadedFiles
     * @throws ReflectionException
     */
    private static function invokeNormalizeUploadedFiles(array $files): array
    {
        $reflection = new ReflectionClass(ServerRequestFactory::class);
        $method = $reflection->getMethod('normalizeUploadedFiles');

        return (array) $method->invoke(null, $files);
    }
}
