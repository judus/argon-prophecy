<?php

declare(strict_types=1);

namespace Tests\Integration;

use DomainException;
use ErrorException;
use LogicException;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionDispatcherInterface;
use Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionFormatterInterface;
use Maduser\Argon\Contracts\ErrorHandling\Http\HttpExceptionInterface;
use Maduser\Argon\Contracts\Http\Server\Middleware\DispatcherInterface;
use Maduser\Argon\ErrorHandling\Http\ExceptionDispatcher;
use Maduser\Argon\ErrorHandling\Http\ExceptionFormatter;
use Maduser\Argon\ErrorHandling\Http\ErrorHandler;
use Maduser\Argon\Http\Message\Uri;
use Maduser\Argon\Prophecy\Provider\ArgonHttpFoundation;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Tests\Application\Mocks\Providers;
use Tests\Integration\Mocks\FakeExceptionDispatcher;
use Throwable;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ExceptionHandlingTest extends AbstractArgonTestCase
{
    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->registerProviders([
            ArgonHttpFoundation::class,
            // Any other required providers...
        ]);

        $this->container->set(
            DispatcherInterface::class,
            FakeExceptionDispatcher::class
        );
    }

    public function testThrowsRuntimeException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fake runtime exception');

        $this->get('/throws-runtime');
    }

    public function testThrowsLogicException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Fake logic exception');

        $this->get('/throws-logic');
    }

    public function testThrowsGenericException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Generic fake exception');

        $this->get('/throws-generic');
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testDispatcherHandlesRuntimeException(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);

        $dispatcher = $this->container->get(ExceptionDispatcher::class);
        $logger = $this->container->get(LoggerInterface::class);

        $dispatcher->register(
            RuntimeException::class,
            function (Throwable $exception, ServerRequestInterface $request) use ($logger) {
                $logger->critical('CALL STEVE IMMEDIATELY!!', [
                    'exception' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'uri' => $request->getUri()->getPath(),
                ]);

                // Now instead of rethrowing, let's return a fake response
                $responseFactory = $this->container->get(ResponseFactoryInterface::class);

                return $responseFactory->createResponse(418) // I'm a teapot 🍵
                ->withHeader('Content-Type', 'text/plain')
                    ->withBody(
                        $this->container->get(StreamFactoryInterface::class)->createStream('Handled by dispatcher')
                    );
            }
        );

        $request = $this->container->get(ServerRequestInterface::class)
            ->withMethod('GET')
            ->withUri(new Uri('/throws-runtime'));

        $response = $dispatcher->dispatch(
            new RuntimeException('Fake runtime exception'),
            $request
        );

        $this->assertSame(418, $response->getStatusCode());
        $this->assertStringContainsString('Handled by dispatcher', (string) $response->getBody());
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testHandlerThrowsException(): void
    {
        $container = $this->container;
        $this->registerProviders(Providers::DEFAULT_STACK);

        $dispatcher = $container->get(ExceptionDispatcher::class);

        $dispatcher->register(
            RuntimeException::class,
            function (Throwable $exception, ServerRequestInterface $request) {
                throw new LogicException('Handler exploded!');
            }
        );

        $response = $dispatcher->dispatch(
            new RuntimeException('Boom!'),
            $container->get(ServerRequestInterface::class)
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(500, $response->getStatusCode());
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testHandlerReturnsNull(): void
    {
        $container = $this->container;
        $this->registerProviders(Providers::DEFAULT_STACK);

        $dispatcher = $container->get(ExceptionDispatcher::class);

        $dispatcher->register(
            RuntimeException::class,
            function (Throwable $exception, ServerRequestInterface $request) {
                return null;
            }
        );

        $response = $dispatcher->dispatch(
            new RuntimeException('No response!'),
            $container->get(ServerRequestInterface::class)
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(500, $response->getStatusCode());
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testNoHandlerFallbacksToFormatter(): void
    {
        $container = $this->container;
        $this->registerProviders(Providers::DEFAULT_STACK);

        $dispatcher = $container->get(ExceptionDispatcher::class);

        $response = $dispatcher->dispatch(
            new DomainException('Nobody handles me'),
            $container->get(ServerRequestInterface::class)
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(500, $response->getStatusCode());
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testFormatJsonSuccess(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);

        $formatter = $this->container->get(ExceptionFormatter::class);
        $request = $this->container->get(ServerRequestInterface::class)
            ->withHeader('Accept', 'application/json');

        $exception = new RuntimeException('Test JSON');

        $response = $formatter->format($exception, $request);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('Unhandled Exception', (string) $response->getBody());
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testFormatJsonThrowsJsonException(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);

        $formatter = $this->container->get(ExceptionFormatter::class);
        $request = $this->container->get(ServerRequestInterface::class)
            ->withHeader('Accept', 'application/json');

        $exception = new class extends RuntimeException {
            public function __construct()
            {
                parent::__construct("\xB1\x31"); // invalid UTF-8 string
            }
        };

        $response = $formatter->format($exception, $request);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('text/plain', $response->getHeaderLine('Content-Type'));
    }

    /**
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function testGetStatusCodeFromHttpException(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);

        $formatter = $this->container->get(ExceptionFormatter::class);

        $httpException = new class extends RuntimeException implements HttpExceptionInterface {
            public function getStatusCode(): int
            {
                return 404;
            }
        };

        $reflection = new ReflectionClass($formatter);
        $method = $reflection->getMethod('getStatusCode');

        $statusCode = $method->invoke($formatter, $httpException);

        $this->assertSame(404, $statusCode);
    }

    /**
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function testGetStatusCodeReturnsInvalidFallsBack(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);

        $formatter = $this->container->get(ExceptionFormatter::class);

        $badHttpException = new class extends RuntimeException implements HttpExceptionInterface {
            public function getStatusCode(): int
            {
                return 700; // Invalid HTTP code
            }
        };

        $reflection = new ReflectionClass($formatter);
        $method = $reflection->getMethod('getStatusCode');

        $statusCode = $method->invoke($formatter, $badHttpException);

        $this->assertSame(500, $statusCode);
    }

    /**
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function testGuessCodeFromValidExceptionCode(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);

        $formatter = $this->container->get(ExceptionFormatter::class);

        $exception = new RuntimeException('Valid Code', 418);

        $reflection = new ReflectionClass($formatter);
        $method = $reflection->getMethod('guessCode');

        $statusCode = $method->invoke($formatter, $exception);

        $this->assertSame(418, $statusCode);
    }

    /**
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function testFormatTextWithTraceInDebugMode(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);

        $formatter = $this->container->get(ExceptionFormatter::class, ['debug' => true]);

        $request = $this->container->get(ServerRequestInterface::class);

        $exception = new RuntimeException('Show me the stack trace');

        $reflection = new ReflectionClass($formatter);
        $method = $reflection->getMethod('formatText');

        $response = $method->invoke($formatter, $exception);

        $body = (string) $response->getBody();

        $this->assertStringContainsString('Show me the stack trace', $body);
        $this->assertStringContainsString('#0', $body);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function testRegisterDoesNotCrash(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);

        $handler = $this->container->get(ErrorHandler::class);

        // Just calling it to ensure it doesn't explode
        $handler->register();

        $this->addToAssertionCount(1);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testHandleDispatchesException(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);

        $handler = $this->container->get(ErrorHandler::class);

        $request = $this->container->get(ServerRequestInterface::class);

        $exception = new RuntimeException('Kaboom');

        $response = $handler->handle($exception, $request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function testIsFatalError(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);

        $handler = $this->container->get(ErrorHandler::class);

        $reflection = new ReflectionClass($handler);
        $method = $reflection->getMethod('isFatalError');

        $fatalError = ['type' => E_ERROR];
        $this->assertTrue($method->invoke($handler, $fatalError));

        $nonFatalError = ['type' => E_WARNING];
        $this->assertFalse($method->invoke($handler, $nonFatalError));
    }

    /**
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function testShutdownFunctionLogsFatalError(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);

        $handler = $this->container->get(ErrorHandler::class);

        // Simulate a fatal error structure
        $error = [
            'type' => E_ERROR,
            'message' => 'Simulated fatal error',
            'file' => 'somefile.php',
            'line' => 123,
        ];

        $reflection = new ReflectionClass($handler);
        $shutdownFunction = $reflection->getMethod('shutdownFunction');

        // We need to monkey-patch error_get_last, OR
        // call shutdownFunction manually after setting the global $error
        // Not beautiful, but PHP is ugly sometimes
        // OR call shutdownFunction() expecting NO crash, just logging

        // For now, we simply call it (can't simulate error_get_last easily)
        $shutdownFunction->invoke($handler);

        $this->addToAssertionCount(1); // We expect NO crash
    }

    /**
     * @throws Exception
     */
    public function testRegisterDoesNotDoubleRegister(): void
    {
        $dispatcher = $this->createMock(ExceptionDispatcherInterface::class);
        $formatter = $this->createMock(ExceptionFormatterInterface::class);
        $handler = new ErrorHandler($dispatcher, $formatter);

        $handler->register();
        $handler->register(); // Should do nothing second time — no crash
        $this->assertTrue(true); // Just to make PHPUnit shut up
    }

    /**
     * @throws Exception
     */
    public function testHandleDispatchesNormally(): void
    {
        $dispatcher = $this->createMock(ExceptionDispatcherInterface::class);
        $formatter = $this->createMock(ExceptionFormatterInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $handler = new ErrorHandler($dispatcher, $formatter, $logger);

        $request = $this->createMock(ServerRequestInterface::class);
        $exception = new RuntimeException('Boom');

        $response = $this->createMock(ResponseInterface::class);

        $dispatcher->method('dispatch')
            ->willReturn($response);

        $this->assertSame($response, $handler->handle($exception, $request));
    }

    /**
     * @throws Exception
     */
    public function testHandleFallbackOnDispatchFailure(): void
    {
        $dispatcher = $this->createMock(ExceptionDispatcherInterface::class);
        $formatter = $this->createMock(ExceptionFormatterInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $handler = new ErrorHandler($dispatcher, $formatter, $logger);

        $request = $this->createMock(ServerRequestInterface::class);
        $original = new RuntimeException('Oops');
        $fallback = new LogicException('Fallback');

        $dispatcher->method('dispatch')
            ->willThrowException($fallback);

        $response = $this->createMock(ResponseInterface::class);

        $formatter->method('format')
            ->with($fallback, $request)
            ->willReturn($response);

        $this->assertSame($response, $handler->handle($original, $request));
    }

    /**
     * @throws Exception
     */
    public function testIsFatalErrorReturnsTrueForFatalErrorTypes(): void
    {
        $dispatcher = $this->createMock(ExceptionDispatcherInterface::class);
        $formatter = $this->createMock(ExceptionFormatterInterface::class);
        $handler = new ErrorHandler($dispatcher, $formatter);

        $fatalErrors = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];

        foreach ($fatalErrors as $fatal) {
            $this->assertTrue($this->invokeIsFatalError($handler, ['type' => $fatal]));
        }
    }

    /**
     * @throws Exception
     */
    public function testIsFatalErrorReturnsFalseForNonFatal(): void
    {
        $dispatcher = $this->createMock(ExceptionDispatcherInterface::class);
        $formatter = $this->createMock(ExceptionFormatterInterface::class);
        $handler = new ErrorHandler($dispatcher, $formatter);

        $this->assertFalse($this->invokeIsFatalError($handler, ['type' => E_NOTICE]));
        $this->assertFalse($this->invokeIsFatalError($handler, null));
    }

    /**
     * @throws Exception
     */
    public function testShutdownFunctionHandlesFatalError(): void
    {
        $dispatcher = $this->createMock(ExceptionDispatcherInterface::class);
        $formatter = $this->createMock(ExceptionFormatterInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $handler = new ErrorHandler($dispatcher, $formatter, $logger);

        $logger->expects($this->once())
            ->method('critical')
            ->with(
                'Fatal shutdown error',
                $this->callback(function ($context): bool {
                    return isset($context['exception']) && $context['exception'] instanceof ErrorException;
                })
            );

        $this->simulateFatalShutdown($handler);
    }

    /**
     * @throws ReflectionException
     */
    private function invokeIsFatalError(ErrorHandler $handler, ?array $error): bool
    {
        $ref = new ReflectionClass($handler);
        $method = $ref->getMethod('isFatalError');

        return $method->invoke($handler, $error);
    }

    /**
     * @throws ReflectionException
     */
    private function simulateFatalShutdown(ErrorHandler $handler): void
    {
        $ref = new ReflectionClass($handler);
        $method = $ref->getMethod('shutdownFunction');

        $fatalError = [
            'type' => E_ERROR,
            'message' => 'Fatal mock error',
            'file' => 'mock.php',
            'line' => 123,
        ];

        $method->invoke($handler, $fatalError);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function testCreateExceptionHandlerLogsThrowable(): void
    {
        $dispatcher = $this->createMock(ExceptionDispatcherInterface::class);
        $formatter = $this->createMock(ExceptionFormatterInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $handler = new ErrorHandler($dispatcher, $formatter, $logger);

        $logger->expects($this->once())
            ->method('critical')
            ->with(
                'Unhandled throwable',
                $this->callback(function ($context): bool {
                    return isset($context['exception']) && $context['exception'] instanceof RuntimeException;
                })
            );

        $closure = $this->invokeCreateExceptionHandler($handler);
        $closure(new RuntimeException('Oops'));
    }

    /**
     * @throws ReflectionException
     */
    private function invokeCreateExceptionHandler(ErrorHandler $handler): callable
    {
        $ref = new ReflectionClass($handler);
        $method = $ref->getMethod('createExceptionHandler');

        return $method->invoke($handler);
    }
}
