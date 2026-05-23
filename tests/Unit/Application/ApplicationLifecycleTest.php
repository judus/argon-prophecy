<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Contracts\Handler\AppHandlerInterface;
use Maduser\Argon\Contracts\Handler\HttpKernelInterface;
use Maduser\Argon\Prophecy\Application;
use Maduser\Argon\Prophecy\Exceptions\ProphecyException;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Application\Mocks\RecordingAppHandler;
use Tests\Unit\Application\Mocks\RecordingHttpKernel;

final class ApplicationLifecycleTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testHandleRunsAndTerminatesGenericApplicationHandler(): void
    {
        $handler = new RecordingAppHandler(23);
        $container = new ArgonContainer();
        $container->set(AppHandlerInterface::class, static fn() => $handler)->shared();

        $application = new Application($container);
        $application->handle();

        self::assertTrue($handler->runCalled);
        self::assertSame(23, $handler->terminateCode);
        self::assertTrue($handler->terminateShouldExit);

        $application->reset();
    }

    #[RunInSeparateProcess]
    public function testProcessReturnsHttpKernelResponse(): void
    {
        $response = new Response(202);
        $request = new ServerRequest('POST', '/jobs');
        $kernel = new RecordingHttpKernel($response);
        $container = new ArgonContainer();
        $container->set(AppHandlerInterface::class, static fn() => $kernel)->shared();
        $container->set(HttpKernelInterface::class, static fn() => $kernel)->shared();

        $application = new Application($container);

        self::assertSame($response, $application->process($request));
        self::assertSame($request, $kernel->processedRequest);

        $application->reset();
    }

    #[RunInSeparateProcess]
    public function testEmitDelegatesToHttpKernel(): void
    {
        $response = new Response(204);
        $kernel = new RecordingHttpKernel();
        $container = new ArgonContainer();
        $container->set(AppHandlerInterface::class, static fn() => $kernel)->shared();
        $container->set(HttpKernelInterface::class, static fn() => $kernel)->shared();

        $application = new Application($container);
        $application->emit($response);

        self::assertSame($response, $kernel->emittedResponse);

        $application->reset();
    }

    #[RunInSeparateProcess]
    public function testProcessRequiresHttpKernel(): void
    {
        $container = new ArgonContainer();
        $container->set(AppHandlerInterface::class, static fn() => new RecordingAppHandler())->shared();

        $application = new Application($container);

        $this->expectException(ProphecyException::class);
        $this->expectExceptionMessage('Active handler does not support HTTP processing.');

        try {
            $application->process();
        } finally {
            $application->reset();
        }
    }

    #[RunInSeparateProcess]
    public function testEmitRequiresHttpKernel(): void
    {
        $container = new ArgonContainer();
        $container->set(AppHandlerInterface::class, static fn() => new RecordingAppHandler())->shared();

        $application = new Application($container);

        $this->expectException(ProphecyException::class);
        $this->expectExceptionMessage('Active handler does not support HTTP emission.');

        try {
            $application->emit(new Response());
        } finally {
            $application->reset();
        }
    }
}
