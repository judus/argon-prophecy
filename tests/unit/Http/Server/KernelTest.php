<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use Maduser\Argon\Contracts\ErrorHandling\Http\ErrorHandlerInterface;
use Maduser\Argon\Contracts\Http\ResponseEmitterInterface;
use Maduser\Argon\Contracts\KernelInterface;
use Maduser\Argon\Http\Kernel;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

final class KernelTest extends TestCase
{
    /**
     * @throws Exception
     */
    private function createStreamMock(string $contents): StreamInterface
    {
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('isSeekable')->willReturn(true);
        $stream->method('rewind')->willReturnCallback(static function (): void {
        });
        $stream->method('eof')->willReturnOnConsecutiveCalls(false, true);
        $stream->method('read')->willReturn($contents);
        $stream->method('getSize')->willReturn(strlen($contents));

        return $stream;
    }

    /**
     * @throws Exception
     */
    private function createResponseMock(string $bodyContent = 'Success', int $statusCode = 200): ResponseInterface
    {
        $body = $this->createStreamMock($bodyContent);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($body);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getHeaders')->willReturn([]);
        $response->method('getHeaderLine')->willReturn('');

        return $response;
    }

    /**
     * @throws Exception
     */
    private function createKernel(
        ?ResponseInterface $response = null,
        bool $debug = true,
        bool $shouldExit = false
    ): KernelInterface {
        $exceptionHandler = $this->createMock(ErrorHandlerInterface::class);
        $exceptionHandler->expects(self::any())->method('register')->willReturnCallback(static function (): void {
        });
        $exceptionHandler->method('handle')->willReturn($this->createResponseMock('Exception caught'));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(\Psr\Http\Message\UriInterface::class);
        $uri->method('__toString')->willReturn('/');

        $request->method('getUri')->willReturn($uri);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response ?? $this->createResponseMock());

        $emitter = $this->createMock(ResponseEmitterInterface::class);
        $emitter->method('emit')->willReturnCallback(function (ResponseInterface $response): void {
            $body = $response->getBody();
            if ($body->isSeekable()) {
                $body->rewind();
            }
            while (!$body->eof()) {
                echo $body->read(8192);
            }
        });

        $logger = $this->createMock(LoggerInterface::class);

        return new Kernel(
            exceptionHandler: $exceptionHandler,
            request: $request,
            handler: $handler,
            emitter: $emitter,
            logger: $logger,
            debug: $debug,
            shouldExit: $shouldExit
        );
    }

    /**
     * @throws Exception
     */
    public function testKernelHandlesAndEmitsSuccessfully(): void
    {
        $kernel = $this->createKernel();

        ob_start();
        $kernel->handle();
        $output = ob_get_clean();

        $this->assertStringContainsString('Success', $output);
    }

    /**
     * @throws Exception
     */
    public function testKernelCaptureReturnsResponse(): void
    {
        $response = $this->createResponseMock('Captured');
        $kernel = $this->createKernel($response);

        $result = $kernel->process();

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
    }

    /**
     * @throws Exception
     */
    public function testKernelEmitsFallbackOnEmitterFailure(): void
    {
        $exceptionHandler = $this->createMock(ErrorHandlerInterface::class);
        $exceptionHandler->expects(self::any())->method('register')->willReturnCallback(static function (): void {
        });
        $exceptionHandler->method('handle')->willReturn($this->createResponseMock('Handled Exception'));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(\Psr\Http\Message\UriInterface::class);
        $uri->method('__toString')->willReturn('/');

        $request->method('getUri')->willReturn($uri);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($this->createResponseMock('Normal Flow'));

        $emitter = $this->createMock(ResponseEmitterInterface::class);
        $emitter->method('emit')->willThrowException(new RuntimeException('Emitter failed'));

        $kernel = new Kernel(
            exceptionHandler: $exceptionHandler,
            request: $request,
            handler: $handler,
            emitter: $emitter,
            logger: null,
            debug: true,
            shouldExit: false
        );

        ob_start();
        $kernel->handle();
        $output = ob_get_clean();

        $this->assertStringContainsString('Fatal error', $output);
    }
}
