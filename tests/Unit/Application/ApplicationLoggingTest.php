<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Contracts\Handler\AppHandlerInterface;
use Maduser\Argon\Prophecy\Application;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
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
}
