<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Contracts\Handler\AppHandlerInterface;
use Maduser\Argon\Prophecy\Application;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tests\Unit\Application\Mocks\CompiledContainerWithFailingServiceMap;
use Tests\Unit\Application\Mocks\CompiledContainerWithServiceMap;
use Tests\Unit\Application\Mocks\RecordingAppHandler;
use Tests\Unit\Application\Mocks\RecordingLogger;

final class ApplicationLoggingTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testContainerDebugLogsExposeMetadataButNotRawParameters(): void
    {
        $logger = new RecordingLogger();
        $container = new ArgonContainer();
        $container->getParameters()->set('apiSecret', 'top-secret-value');
        $container->set(AppHandlerInterface::class, static fn() => new RecordingAppHandler())->shared();

        $application = new Application($container, $logger);
        $application->handle();
        $application->reset();

        $debugRecords = array_values(array_filter(
            $logger->records,
            static fn(array $record): bool => $record['level'] === 'debug'
        ));

        self::assertNotSame([], $debugRecords);

        foreach ($debugRecords as $record) {
            self::assertArrayHasKey('parameterCount', $record['context']);
            self::assertArrayHasKey('bindingCount', $record['context']);
            self::assertArrayNotHasKey('parameters', $record['context']);
            self::assertArrayNotHasKey('bindings', $record['context']);
            self::assertStringNotContainsString('top-secret-value', serialize($record['context']));
        }
    }

    #[RunInSeparateProcess]
    public function testContainerLoggerReplacesConstructorLoggerDuringBootstrap(): void
    {
        $constructorLogger = new RecordingLogger();
        $containerLogger = new RecordingLogger();
        $container = new ArgonContainer();
        $container->set(LoggerInterface::class, static fn() => $containerLogger)->shared();
        $container->set(AppHandlerInterface::class, static fn() => new RecordingAppHandler())->shared();

        $application = new Application($container, $constructorLogger);
        $application->handle();
        $application->reset();

        self::assertSame([], $constructorLogger->records);
        self::assertNotSame([], $containerLogger->records);
    }

    #[RunInSeparateProcess]
    public function testCompiledContainerDebugLogsIncludeServiceMapCount(): void
    {
        $logger = new RecordingLogger();
        $container = new CompiledContainerWithServiceMap();
        $container->set(AppHandlerInterface::class, static fn() => new RecordingAppHandler())->shared();

        $application = new Application($container, $logger);
        $application->handle();
        $application->reset();

        foreach ($this->debugRecords($logger) as $record) {
            self::assertTrue($record['context']['compiled']);
            self::assertSame(2, $record['context']['serviceMapCount']);
            self::assertArrayNotHasKey('serviceMap', $record['context']);
        }
    }

    #[RunInSeparateProcess]
    public function testCompiledContainerDebugLogsWhenServiceMapIsUnavailable(): void
    {
        $logger = new RecordingLogger();
        $container = new CompiledContainerWithFailingServiceMap();
        $container->set(AppHandlerInterface::class, static fn() => new RecordingAppHandler())->shared();

        $application = new Application($container, $logger);
        $application->handle();
        $application->reset();

        foreach ($this->debugRecords($logger) as $record) {
            self::assertTrue($record['context']['compiled']);
            self::assertFalse($record['context']['serviceMapAvailable']);
            self::assertArrayNotHasKey('serviceMapCount', $record['context']);
        }
    }

    /**
     * @return list<array{level: mixed, message: string, context: array<array-key, mixed>}>
     */
    private function debugRecords(RecordingLogger $logger): array
    {
        $debugRecords = array_values(array_filter(
            $logger->records,
            static fn(array $record): bool => $record['level'] === 'debug'
        ));

        self::assertNotSame([], $debugRecords);

        return $debugRecords;
    }
}
