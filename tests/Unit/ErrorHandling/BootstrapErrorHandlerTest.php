<?php

declare(strict_types=1);

namespace Tests\Unit\ErrorHandling;

use Closure;
use Maduser\Argon\Prophecy\ErrorHandling\BootstrapErrorHandler;
use Maduser\Argon\Prophecy\ErrorHandling\BootstrapErrorHandlerMode;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

final class BootstrapErrorHandlerTest extends TestCase
{
    private string $capturedOutput;
    /** @var MockObject&LoggerInterface $logger */
    private LoggerInterface $logger;

    /**
     * @throws Exception
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->capturedOutput = '';
        /** @var MockObject&LoggerInterface $logger */
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    private function createHandler(
        ?array $fakeError = null,
        string $sapi = 'cli',
        ?Closure $outputCallback = null
    ): BootstrapErrorHandler {
        return new BootstrapErrorHandler(
            $this->logger,
            $outputCallback ?? function (string $message): void {
                $this->capturedOutput .= $message;
            },
            function (int $code): void {
                throw new RuntimeException('Fake terminate ' . $code);
            },
            function () use ($fakeError): ?array {
                return $fakeError;
            },
            $sapi
        );
    }

    public function testHandleExceptionLogsAndOutputs(): void
    {
        $handler = $this->createHandler(null, 'cli');

        $exception = new RuntimeException('Test exception');

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Unhandled bootstrap exception', $this->callback(function ($context) {
                return isset($context['message'], $context['file'], $context['line'], $context['trace']);
            }));

        try {
            $handler->handleException($exception);
        } catch (Throwable) {
            // expected fake terminate
        }

        $this->assertStringContainsString('Fatal error: RuntimeException', $this->capturedOutput);
        $this->assertStringContainsString('Message: Test exception', $this->capturedOutput);
        $this->assertStringContainsString('Location:', $this->capturedOutput);
        $this->assertStringContainsString("Trace:\n", $this->capturedOutput);
    }

    public function testHandleExceptionOutputsHtmlForWeb(): void
    {
        $this->capturedOutput = '';

        $handler = new BootstrapErrorHandler(
            $this->logger,
            null, // <-- important: use default callback
            function (int $code): void {
                throw new RuntimeException('Fake terminate ' . $code);
            },
            static fn() => null,
            'apache' // Simulate non-CLI
        );

        $exception = new RuntimeException('Test Web Exception');

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Unhandled bootstrap exception', $this->callback(function ($context) {
                return isset($context['message']);
            }));

        // Now capture the real output
        ob_start();
        try {
            $handler->handleException($exception);
        } catch (Throwable) {
            // ignore fake terminate
        }
        $output = ob_get_clean();
        self::assertIsString($output);

        $this->assertStringContainsString('<pre>', $output);
        $this->assertStringContainsString('Test Web Exception', $output);
    }

    public function testHandleExceptionOutputsToStderrForCli(): void
    {
        $stream = fopen('php://memory', 'w+');
        if ($stream === false) {
            throw new RuntimeException('Could not open in-memory test stream.');
        }

        $handler = new BootstrapErrorHandler(
            $this->logger,
            null, // use default output callback
            function (int $code): void {
                throw new RuntimeException('Fake terminate ' . $code);
            },
            static fn() => null,
            'cli',
            $stream
        );

        $exception = new RuntimeException('Test CLI Exception');

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Unhandled bootstrap exception', $this->callback(function ($context) {
                return isset($context['message']);
            }));

        try {
            $handler->handleException($exception);
        } catch (Throwable) {
            // ignore fake terminate
        }

        rewind($stream);
        $output = stream_get_contents($stream);
        self::assertIsString($output);

        $this->assertStringContainsString('Fatal error: RuntimeException', $output);
        $this->assertStringContainsString('Message: Test CLI Exception', $output);
        $this->assertStringContainsString('Location:', $output);
    }

    public function testExplicitOutputModeOverridesDetection(): void
    {
        $handler = new BootstrapErrorHandler(
            $this->logger,
            null,
            function (int $code): void {
                throw new RuntimeException('terminate');
            },
            static fn() => null,
            'cli'
        );

        $handler->setOutputMode(BootstrapErrorHandlerMode::HTTP);

        ob_start();
        try {
            $handler->handleException(new RuntimeException('Mode override'));
        } catch (RuntimeException) {
            // expected fake terminate
        }

        $output = ob_get_clean();
        self::assertIsString($output);

        $this->assertStringContainsString('<pre>', $output);
    }

    public function testCliServerSapiOutputsPlainHttpResponse(): void
    {
        $handler = new BootstrapErrorHandler(
            $this->logger,
            null,
            function (int $code): void {
                throw new RuntimeException('Fake terminate ' . $code);
            },
            static fn() => null,
            'cli-server'
        );

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Unhandled bootstrap exception', $this->callback(function ($context) {
                return isset($context['message'])
                    && $context['message'] === 'Test CLI Server Exception';
            }));

        $previousStatusCode = http_response_code();
        $initialOutputBufferLevel = ob_get_level();

        ob_start();
        try {
            try {
                $handler->handleException(new RuntimeException('Test CLI Server Exception'));
            } catch (RuntimeException) {
                // expected fake terminate
            }

            $output = ob_get_clean();
            self::assertIsString($output);

            $this->assertStringContainsString('Fatal error: RuntimeException', $output);
            $this->assertStringContainsString('Message: Test CLI Server Exception', $output);
            $this->assertStringNotContainsString('<pre>', $output);
            $this->assertSame(500, http_response_code());
        } finally {
            while (ob_get_level() > $initialOutputBufferLevel) {
                ob_end_clean();
            }

            if (is_int($previousStatusCode)) {
                http_response_code($previousStatusCode);
            }
        }
    }

    public function testHandleShutdownOutputsFatalError(): void
    {
        $handler = $this->createHandler([
            'type' => E_ERROR,
            'message' => 'Fatal shutdown failure',
            'file' => __FILE__,
            'line' => __LINE__,
        ]);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Unhandled bootstrap exception', $this->callback(function ($context) {
                return isset($context['message'])
                    && $context['message'] === 'Fatal shutdown failure';
            }));

        $handler->register();

        try {
            $handler->handleShutdown();
        } catch (RuntimeException) {
            // expected fake terminate
        } finally {
            $handler->unregister();
        }

        $this->assertStringContainsString('Fatal shutdown failure', $this->capturedOutput);
    }

    public function testHandleShutdownIgnoresNonFatalError(): void
    {
        $handler = $this->createHandler([
            'type' => E_WARNING,
            'message' => 'Non-fatal warning',
            'file' => __FILE__,
            'line' => __LINE__,
        ]);

        $this->logger->expects($this->never())
            ->method('error');

        $handler->register();

        try {
            $handler->handleShutdown();
        } finally {
            $handler->unregister();
        }

        $this->assertSame('', $this->capturedOutput);
    }

    public function testUnregisterMakesShutdownHandlerInert(): void
    {
        $handler = $this->createHandler([
            'type' => E_ERROR,
            'message' => 'Fatal shutdown failure',
            'file' => __FILE__,
            'line' => __LINE__,
        ]);

        $handler->register();
        $handler->unregister();

        $this->logger->expects($this->never())
            ->method('error');

        $handler->handleShutdown();

        $this->assertSame('', $this->capturedOutput);
    }

    public function testHandleErrorConvertsAndLogs(): void
    {
        $handler = $this->createHandler(null, 'cli');

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Unhandled bootstrap exception', $this->callback(function ($context) {
                return $context['message'] === 'Warning simulated';
            }));

        try {
            $handler->handleError(E_WARNING, 'Warning simulated', __FILE__, __LINE__);
        } catch (Throwable) {
            // expected fake terminate
        }

        $this->assertStringContainsString('Fatal error: ErrorException', $this->capturedOutput);
        $this->assertStringContainsString('Message: Warning simulated', $this->capturedOutput);
        $this->assertStringContainsString('Location:', $this->capturedOutput);
    }

    public function testHandleShutdownHandlesFatalError(): void
    {
        $fakeError = [
            'type' => E_ERROR,
            'message' => 'Simulated fatal error',
            'file' => 'fake.php',
            'line' => 123,
        ];

        $handler = $this->createHandler($fakeError, 'cli');

        $handler->register();

        try {
            $handler->handleShutdown();
        } catch (Throwable) {
            // expected fake terminate
        } finally {
            $handler->unregister();
        }

        $this->assertStringContainsString('Fatal error: ErrorException', $this->capturedOutput);
        $this->assertStringContainsString('Message: Simulated fatal error', $this->capturedOutput);
        $this->assertStringContainsString('Location: fake.php:123', $this->capturedOutput);
    }

    public function testHandleShutdownWithoutErrorDoesNothing(): void
    {
        $handler = $this->createHandler(null, 'cli');

        $handler->register();

        try {
            $handler->handleShutdown();
        } catch (Throwable) {
            // terminate not expected here
        } finally {
            $handler->unregister();
        }

        $this->assertSame('', $this->capturedOutput);
    }
}
