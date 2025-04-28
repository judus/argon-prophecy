<?php

declare(strict_types=1);

namespace Tests\Integration\Prophecy\Provider;

use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionFormatterInterface;
use Maduser\Argon\Contracts\ErrorHandling\Http\ErrorHandlerInterface;
use Maduser\Argon\Contracts\Http\Server\Factory\RequestHandlerFactoryInterface;
use Maduser\Argon\Contracts\Http\Server\Middleware\DispatcherInterface;
use Maduser\Argon\Contracts\Http\Server\Middleware\HtmlResponderInterface;
use Maduser\Argon\Contracts\Http\Server\Middleware\JsonResponderInterface;
use Maduser\Argon\Contracts\Http\Server\Middleware\PlainTextResponderInterface;
use Maduser\Argon\Contracts\Http\Server\Middleware\ResponseResponderInterface;
use Maduser\Argon\Contracts\Http\Server\ResultContextInterface;
use Maduser\Argon\Contracts\KernelInterface;
use Maduser\Argon\ErrorHandling\Http\ExceptionDispatcher;
use Maduser\Argon\ErrorHandling\Http\ExceptionFormatter;
use Maduser\Argon\ErrorHandling\Http\ErrorHandler;
use Maduser\Argon\Http\Kernel;
use Maduser\Argon\Prophecy\Provider\ArgonHttpFoundation;
use Maduser\Argon\Logging\LoggerServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ServiceProviderTest extends TestCase
{
    private ArgonContainer $container;

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new ArgonContainer();
        $this->container->register(ArgonHttpFoundation::class);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testServicesAreRegistered(): void
    {
        $this->assertInstanceOf(LoggerInterface::class, $this->container->get(LoggerInterface::class));

        $this->assertInstanceOf(ExceptionFormatter::class, $this->container->get(ExceptionFormatterInterface::class));
        $this->assertInstanceOf(ErrorHandler::class, $this->container->get(ErrorHandlerInterface::class));
        $this->assertInstanceOf(ExceptionDispatcher::class, $this->container->get(ExceptionDispatcher::class));

        $this->assertInstanceOf(Kernel::class, $this->container->get(KernelInterface::class));

        $this->assertInstanceOf(
            ServerRequestFactoryInterface::class,
            $this->container->get(ServerRequestFactoryInterface::class)
        );
        $this->assertInstanceOf(
            ServerRequestInterface::class,
            $this->container->get(ServerRequestInterface::class)
        );
        $this->assertInstanceOf(
            ResponseFactoryInterface::class,
            $this->container->get(ResponseFactoryInterface::class)
        );
        $this->assertInstanceOf(ResponseInterface::class, $this->container->get(ResponseInterface::class));
        $this->assertInstanceOf(StreamFactoryInterface::class, $this->container->get(StreamFactoryInterface::class));
        $this->assertInstanceOf(StreamInterface::class, $this->container->get(StreamInterface::class));
        $this->assertInstanceOf(UriFactoryInterface::class, $this->container->get(UriFactoryInterface::class));
        $this->assertInstanceOf(UriInterface::class, $this->container->get(UriInterface::class));
        $this->assertInstanceOf(
            UploadedFileFactoryInterface::class,
            $this->container->get(UploadedFileFactoryInterface::class)
        );

        $factory = $this->container->get(UploadedFileFactoryInterface::class);
        $stream = $this->container->get(StreamFactoryInterface::class)->createStream('test');
        $uploadedFile = $factory->createUploadedFile($stream);
        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFile);

        $this->assertInstanceOf(
            RequestHandlerFactoryInterface::class,
            $this->container->get(RequestHandlerFactoryInterface::class)
        );
        $this->assertInstanceOf(RequestHandlerInterface::class, $this->container->get(RequestHandlerInterface::class));
        $this->assertInstanceOf(ResultContextInterface::class, $this->container->get(ResultContextInterface::class));

        $this->assertInstanceOf(DispatcherInterface::class, $this->container->get(DispatcherInterface::class));
        $this->assertInstanceOf(JsonResponderInterface::class, $this->container->get(JsonResponderInterface::class));
        $this->assertInstanceOf(HtmlResponderInterface::class, $this->container->get(HtmlResponderInterface::class));
        $this->assertInstanceOf(
            PlainTextResponderInterface::class,
            $this->container->get(PlainTextResponderInterface::class)
        );
        $this->assertInstanceOf(
            ResponseResponderInterface::class,
            $this->container->get(ResponseResponderInterface::class)
        );
    }

    public function testLoggerRegisters(): void
    {
        $this->container->register(LoggerServiceProvider::class);
        $this->assertInstanceOf(LoggerInterface::class, $this->container->get(LoggerInterface::class));
    }
}
