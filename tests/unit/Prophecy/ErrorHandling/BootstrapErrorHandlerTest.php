<?php

declare(strict_types=1);

namespace Tests\Unit\Prophecy\ErrorHandling;

use Closure;
use ErrorException;
use Maduser\Argon\Prophecy\ErrorHandling\BootstrapErrorHandler;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class BootstrapErrorHandlerTest extends TestCase
{
    private string $capturedOutput;
    private LoggerInterface $logger;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->capturedOutput = '';
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

        $this->assertStringContainsString('Fatal error: Test exception', $this->capturedOutput);
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

        $this->assertStringContainsString('<pre>', $output);
        $this->assertStringContainsString('Test Web Exception', $output);
    }

    public function testHandleExceptionOutputsToStderrForCli(): void
    {
        $stream = fopen('php://memory', 'w+');

        $handler = new BootstrapErrorHandler(
            $this->logger,
            null, // use default output callback
            function (int $code): void {
                throw new RuntimeException('Fake terminate ' . $code);
            },
            static fn() => null,
            'cli',
            $stream // 👈 inject stream
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

        $this->assertStringContainsString('Fatal error: Test CLI Exception', $output);
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

        $this->assertStringContainsString('Fatal error: Warning simulated', $this->capturedOutput);
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

        try {
            $handler->handleShutdown();
        } catch (Throwable) {
            // expected fake terminate
        }

        $this->assertStringContainsString('Fatal error: Simulated fatal error', $this->capturedOutput);
    }

    public function testHandleShutdownWithoutErrorDoesNothing(): void
    {
        $handler = $this->createHandler(null, 'cli');

        try {
            $handler->handleShutdown();
        } catch (Throwable) {
            // terminate not expected here
        }

        $this->assertSame('', $this->capturedOutput);
    }
}
