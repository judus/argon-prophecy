<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Maduser\Argon\Contracts\Handler\AppHandlerInterface;
use Maduser\Argon\Contracts\Handler\HttpKernelInterface;
use Maduser\Argon\Prophecy\Application;
use Maduser\Argon\Support\Contracts\ErrorHandlerInterface;
use Maduser\Argon\Container\ArgonContainer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;

final class ApplicationErrorHandlingTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testRegistersRuntimeErrorHandlerWhenBound(): void
    {
        $errorHandler = new RecordingErrorHandler(new Response());
        $kernel = new NoOpHttpKernel();

        $container = new ArgonContainer();
        $container->set(ErrorHandlerInterface::class, static fn() => $errorHandler)->shared();
        $container->set(HttpKernelInterface::class, static fn() => $kernel)->shared();
        $container->set(AppHandlerInterface::class, static fn() => $kernel)->shared();

        $application = new Application($container);
        $application->handle();

        self::assertSame(1, $errorHandler->registerCount);
        self::assertTrue($kernel->handleCalled);
        self::assertNull($errorHandler->lastException);
    }

    /**
     * @runInSeparateProcess
     */
    public function testHttpExceptionDelegatesToRuntimeErrorHandler(): void
    {
        $response = new Response(500);
        $errorHandler = new RecordingErrorHandler($response);
        $kernel = new ThrowingHttpKernel();
        $request = new ServerRequest('GET', '/test');

        $container = new ArgonContainer();
        $container->set(ServerRequestInterface::class, static fn() => $request)->shared();
        $container->set(ErrorHandlerInterface::class, static fn() => $errorHandler)->shared();
        $container->set(HttpKernelInterface::class, static fn() => $kernel)->shared();
        $container->set(AppHandlerInterface::class, static fn() => $kernel)->shared();

        $application = new Application($container);
        $application->handle($request);

        self::assertSame($response, $kernel->emittedResponse);
        self::assertTrue($kernel->terminateCalled);
        self::assertInstanceOf(Throwable::class, $errorHandler->lastException);
        self::assertSame($request, $errorHandler->lastRequest);
    }
}

final class RecordingErrorHandler implements ErrorHandlerInterface
{
    public int $registerCount = 0;
    public ?Throwable $lastException = null;
    public ?ServerRequestInterface $lastRequest = null;

    public function __construct(private readonly ResponseInterface $response)
    {
    }

    public function register(): void
    {
        $this->registerCount++;
    }

    public function handle(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        $this->lastException = $e;
        $this->lastRequest = $request;

        return $this->response;
    }
}

final class NoOpHttpKernel implements HttpKernelInterface
{
    public bool $handleCalled = false;

    public function handle(?ServerRequestInterface $request = null): void
    {
        $this->handleCalled = true;
    }

    public function process(?ServerRequestInterface $request = null): ResponseInterface
    {
        throw new RuntimeException('process() should not be called during this test.');
    }

    public function emit(ResponseInterface $response): void
    {
        // no-op
    }

    public function run(): int
    {
        return 0;
    }

    public function terminate(int $code = 0, bool $shouldExit = true): void
    {
        // no-op
    }
}

final class ThrowingHttpKernel implements HttpKernelInterface
{
    public ?ResponseInterface $emittedResponse = null;
    public bool $terminateCalled = false;

    public function handle(?ServerRequestInterface $request = null): void
    {
        throw new RuntimeException('kernel failure');
    }

    public function process(?ServerRequestInterface $request = null): ResponseInterface
    {
        throw new RuntimeException('kernel failure');
    }

    public function emit(ResponseInterface $response): void
    {
        $this->emittedResponse = $response;
    }

    public function run(): int
    {
        throw new RuntimeException('not used');
    }

    public function terminate(int $code = 0, bool $shouldExit = true): void
    {
        $this->terminateCalled = true;
    }
}
