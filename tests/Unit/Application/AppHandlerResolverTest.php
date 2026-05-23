<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Application;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Contracts\Handler\AppHandlerInterface;
use Maduser\Argon\Contracts\Handler\HttpKernelInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Tests\Unit\Application\Mocks\RecordingAppHandler;
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

    public function testFallsBackToHttpKernelWhenExplicitBindingIsInvalid(): void
    {
        $httpKernel = new RecordingHttpKernel();

        $container = new ArgonContainer();
        $container->set(AppHandlerInterface::class, static fn() => new stdClass())->shared();
        $container->set(HttpKernelInterface::class, static fn() => $httpKernel)->shared();

        self::assertSame($httpKernel, (new AppHandlerResolver())->resolve($container));
    }

    public function testThrowsWhenNoHandlerIsRegistered(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No application handler registered.');

        (new AppHandlerResolver())->resolve(new ArgonContainer());
    }
}
