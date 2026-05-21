<?php

declare(strict_types=1);

namespace Tests\Unit\Logging;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Logging\LoggerServiceProvider;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class LoggerServiceProviderTest extends TestCase
{
    public function testRegisterBindsLoggerInterfaceWhenMonologIsAvailable(): void
    {
        $container = new ArgonContainer();

        $container->register(LoggerServiceProvider::class);

        $this->assertInstanceOf(Logger::class, $container->get(LoggerInterface::class));
    }

    public function testRegisterFallsBackToNullLoggerWhenLoggerClassesAreUnavailable(): void
    {
        $container = new ArgonContainer();
        $provider = new LoggerServiceProvider(
            loggerClass: '\Nonexistent\Logger',
            handlerClass: '\Nonexistent\Handler'
        );

        $provider->register($container);

        $this->assertInstanceOf(NullLogger::class, $container->get(LoggerInterface::class));
    }
}
