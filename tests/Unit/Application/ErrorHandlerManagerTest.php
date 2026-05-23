<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Application;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Prophecy\ErrorHandling\BootstrapErrorHandlerMode;
use Maduser\Argon\Support\Contracts\ErrorHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Tests\Unit\Application\Mocks\RecordingAppHandler;
use Tests\Unit\Application\Mocks\RecordingBootstrapErrorHandler;
use Tests\Unit\Application\Mocks\RecordingCliKernel;
use Tests\Unit\Application\Mocks\RecordingErrorHandler;
use Tests\Unit\Application\Mocks\RecordingHttpKernel;

final class ErrorHandlerManagerTest extends TestCase
{
    public function testConfiguresCliBootstrapModeForCliHandlers(): void
    {
        $bootstrapHandler = new RecordingBootstrapErrorHandler();
        $bootstrapHandler->setOutputMode(BootstrapErrorHandlerMode::HTTP);

        (new ErrorHandlerManager($bootstrapHandler))->configureBootstrapModeFor(new RecordingCliKernel());

        self::assertSame(BootstrapErrorHandlerMode::CLI, $bootstrapHandler->outputMode);
    }

    public function testConfiguresHttpBootstrapModeForNonCliHandlers(): void
    {
        $bootstrapHandler = new RecordingBootstrapErrorHandler();
        $bootstrapHandler->setOutputMode(BootstrapErrorHandlerMode::CLI);

        (new ErrorHandlerManager($bootstrapHandler))->configureBootstrapModeFor(new RecordingAppHandler());

        self::assertSame(BootstrapErrorHandlerMode::HTTP, $bootstrapHandler->outputMode);
    }

    public function testHttpThrowableFallsBackToBootstrapHandlerWhenRuntimeHandlerIsMissing(): void
    {
        $throwable = new RuntimeException('boom');
        $bootstrapHandler = new RecordingBootstrapErrorHandler();
        $manager = new ErrorHandlerManager($bootstrapHandler);

        self::assertNull(
            $manager->handleHttpThrowable($throwable, new ServerRequest('GET', '/'), new ArgonContainer())
        );
        self::assertSame($throwable, $bootstrapHandler->lastException);
    }

    public function testHttpThrowableUsesRuntimeHandlerWithRequestResolvedFromContainer(): void
    {
        $throwable = new RuntimeException('boom');
        $request = new ServerRequest('GET', '/from-container');
        $response = new Response(418);
        $runtimeHandler = new RecordingErrorHandler($response);
        $bootstrapHandler = new RecordingBootstrapErrorHandler();
        $container = new ArgonContainer();
        $container->set(ServerRequestInterface::class, static fn() => $request)->shared();
        $container->set(ErrorHandlerInterface::class, static fn() => $runtimeHandler)->shared();

        $manager = new ErrorHandlerManager($bootstrapHandler);
        $manager->registerRuntimeHandlerIfAvailable($container);

        self::assertSame($response, $manager->handleHttpThrowable($throwable, null, $container));
        self::assertSame($throwable, $runtimeHandler->lastException);
        self::assertSame($request, $runtimeHandler->lastRequest);
        self::assertNull($bootstrapHandler->lastException);
    }

    public function testHttpThrowableFallsBackWhenRuntimeHandlerFails(): void
    {
        $throwable = new RuntimeException('kernel failure');
        $bootstrapHandler = new RecordingBootstrapErrorHandler();
        $container = new ArgonContainer();
        $container->set(
            ErrorHandlerInterface::class,
            static fn() => new class implements ErrorHandlerInterface {
                #[\Override]
                public function register(): void
                {
                    // no-op
                }

                #[\Override]
                public function handle(
                    \Throwable $e,
                    ServerRequestInterface $request
                ): \Psr\Http\Message\ResponseInterface {
                    throw new RuntimeException('handler failure');
                }
            }
        )->shared();

        $manager = new ErrorHandlerManager($bootstrapHandler);
        $manager->registerRuntimeHandlerIfAvailable($container);

        self::assertNull($manager->handleHttpThrowable($throwable, new ServerRequest('GET', '/'), $container));
        self::assertSame($throwable, $bootstrapHandler->lastException);
    }

    public function testEmitResponseTerminatesSuccessfulResponsesWithZeroExitCode(): void
    {
        $kernel = new RecordingHttpKernel();
        $response = new Response(204);

        (new ErrorHandlerManager(new RecordingBootstrapErrorHandler()))->emitResponse($kernel, $response);

        self::assertSame($response, $kernel->emittedResponse);
        self::assertSame(0, $kernel->terminateCode);
    }

    public function testEmitResponseTerminatesServerErrorsWithFailureExitCode(): void
    {
        $kernel = new RecordingHttpKernel();
        $response = new Response(500);

        (new ErrorHandlerManager(new RecordingBootstrapErrorHandler()))->emitResponse($kernel, $response);

        self::assertSame($response, $kernel->emittedResponse);
        self::assertSame(1, $kernel->terminateCode);
    }

    public function testCliThrowableDelegatesToBootstrapHandler(): void
    {
        $throwable = new RuntimeException('cli failure');
        $bootstrapHandler = new RecordingBootstrapErrorHandler();

        (new ErrorHandlerManager($bootstrapHandler))->handleCliThrowable($throwable);

        self::assertSame($throwable, $bootstrapHandler->lastException);
    }
}
