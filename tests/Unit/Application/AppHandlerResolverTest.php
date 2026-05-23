<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Application;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Contracts\Handler\AppHandlerInterface;
use Maduser\Argon\Contracts\Handler\CliKernelInterface;
use Maduser\Argon\Contracts\Handler\HttpKernelInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Tests\Unit\Application\Mocks\RecordingAppHandler;
use Tests\Unit\Application\Mocks\RecordingCliKernel;
use Tests\Unit\Application\Mocks\RecordingHttpKernel;

final class AppHandlerResolverTest extends TestCase
{
    public function testResolvesExplicitApplicationHandler(): void
    {
        $handler = new RecordingAppHandler();
        $container = new ArgonContainer();
        $container->set(AppHandlerInterface::class, static fn() => $handler)->shared();

        self::assertSame($handler, (new AppHandlerResolver())->resolve($container));
    }

    public function testPrefersExplicitApplicationHandlerOverHttpKernel(): void
    {
        $handler = new RecordingAppHandler();
        $httpKernel = new RecordingHttpKernel();

        $container = new ArgonContainer();
        $container->set(AppHandlerInterface::class, static fn() => $handler)->shared();
        $container->set(HttpKernelInterface::class, static fn() => $httpKernel)->shared();

        self::assertSame($handler, (new AppHandlerResolver())->resolve($container));
    }

    public function testResolvesSingleHttpKernelBinding(): void
    {
        $httpKernel = new RecordingHttpKernel();

        $container = new ArgonContainer();
        $container->set(HttpKernelInterface::class, static fn() => $httpKernel)->shared();

        self::assertSame($httpKernel, (new AppHandlerResolver())->resolve($container));
    }

    public function testResolvesSingleCliKernelBinding(): void
    {
        $cliKernel = new RecordingCliKernel();

        $container = new ArgonContainer();
        $container->set(CliKernelInterface::class, static fn() => $cliKernel)->shared();

        self::assertSame($cliKernel, (new AppHandlerResolver())->resolve($container));
    }

    public function testExplicitApplicationHandlerBindingMustResolveToApplicationHandler(): void
    {
        $httpKernel = new RecordingHttpKernel();

        $container = new ArgonContainer();
        $container->set(AppHandlerInterface::class, static fn() => new stdClass())->shared();
        $container->set(HttpKernelInterface::class, static fn() => $httpKernel)->shared();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Application handler binding');
        $this->expectExceptionMessage(AppHandlerInterface::class);

        (new AppHandlerResolver())->resolve($container);
    }

    public function testLifecycleHandlerBindingMustResolveToApplicationHandler(): void
    {
        $container = new ArgonContainer();
        $container->set(HttpKernelInterface::class, static fn() => new stdClass())->shared();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Application handler binding');
        $this->expectExceptionMessage(HttpKernelInterface::class);

        (new AppHandlerResolver())->resolve($container);
    }

    public function testMultipleLifecycleHandlersRequireExplicitApplicationHandler(): void
    {
        $container = new ArgonContainer();
        $container->set(HttpKernelInterface::class, static fn() => new RecordingHttpKernel())->shared();
        $container->set(CliKernelInterface::class, static fn() => new RecordingCliKernel())->shared();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Multiple application handlers registered');
        $this->expectExceptionMessage(AppHandlerInterface::class);

        (new AppHandlerResolver())->resolve($container);
    }

    public function testThrowsWhenNoHandlerIsRegistered(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No application handler registered.');

        (new AppHandlerResolver())->resolve(new ArgonContainer());
    }
}
