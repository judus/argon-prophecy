<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Maduser\Argon\Contracts\Handler\AppHandlerInterface;
use Maduser\Argon\Contracts\Handler\HttpKernelInterface;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Prophecy\Application;
use Maduser\Argon\Support\Contracts\ErrorHandlerInterface;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Unit\Application\Mocks\NoOpHttpKernel;
use Tests\Unit\Application\Mocks\RecordingErrorHandler;
use Tests\Unit\Application\Mocks\ThrowingHttpKernel;
use Throwable;

final class ApplicationErrorHandlingTest extends TestCase
{
    #[RunInSeparateProcess]
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

    #[RunInSeparateProcess]
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
