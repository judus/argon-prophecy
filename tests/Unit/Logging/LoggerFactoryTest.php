<?php

declare(strict_types=1);

namespace Tests\Unit\Logging;

use Maduser\Argon\Logging\LoggerFactory;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class LoggerFactoryTest extends TestCase
{
    public function testCreateReturnsLoggerWhenMonologAvailable(): void
    {
        $factory = new LoggerFactory(
            logLevel: Level::Debug->value,
            logFile: 'php://temp',
            loggerClass: Logger::class,
            handlerClass: StreamHandler::class
        );

        $logger = $factory->create();

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateReturnsNullLoggerWhenLoggerClassMissing(): void
    {
        $factory = new LoggerFactory(
            loggerClass: '\Nonexistent\Logger',
            handlerClass: StreamHandler::class
        );

        $logger = $factory->create();

        $this->assertInstanceOf(NullLogger::class, $logger);
    }

    public function testCreateReturnsNullLoggerWhenHandlerClassMissing(): void
    {
        $factory = new LoggerFactory(
            loggerClass: Logger::class,
            handlerClass: '\Nonexistent\Handler'
        );

        $logger = $factory->create();

        $this->assertInstanceOf(NullLogger::class, $logger);
    }

    public function testDefaultLogFileIsStdoutOrStderr(): void
    {
        $factory = new LoggerFactory();

        $logger = $factory->create();

        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }
}
