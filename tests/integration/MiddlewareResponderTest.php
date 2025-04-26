<?php

declare(strict_types=1);

namespace Tests\Integration;

use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Contracts\Http\Server\Middleware\DispatcherInterface;
use Maduser\Argon\Http\Server\Exception\EmptyMiddlewareChainException;
use PHPUnit\Framework\TestCase;
use Tests\Application\Mocks\Providers;
use Tests\Integration\Mocks\FakeResponderDispatcher;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class MiddlewareResponderTest extends AbstractArgonTestCase
{
    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testJsonResponderHandlesArray(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);
        $this->container->set(DispatcherInterface::class, FakeResponderDispatcher::class);

        $data = $this->getJson('/json');

        $this->assertArrayHasKey('key', $data);
        $this->assertSame('value', $data['key']);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testJsonResponderHandlesJsonSerializable(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);
        $this->container->set(DispatcherInterface::class, FakeResponderDispatcher::class);

        $data = $this->getJson('/json-serializable');

        $this->assertArrayHasKey('mocked', $data);
        $this->assertTrue($data['mocked']);
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testHtmlResponderReturnsHtml(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);
        $this->container->set(DispatcherInterface::class, FakeResponderDispatcher::class);

        $response = $this->get('/html');

        $this->assertStatus($response, 200);
        $this->assertStringContainsString('<p>HTML Mock</p>', (string) $response->getBody());
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testPlainTextResponderReturnsPlainText(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);
        $this->container->set(DispatcherInterface::class, FakeResponderDispatcher::class);

        $response = $this->get('/plain');

        $this->assertStatus($response, 200);
        $this->assertStringContainsString('Plain text response.', (string) $response->getBody());
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testResponseResponderReturnsResponse(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);
        $this->container->set(DispatcherInterface::class, FakeResponderDispatcher::class);

        $response = $this->get('/response');

        $this->assertStatus($response, 200);
        $this->assertStringContainsString('Real Response', (string) $response->getBody());
    }

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function testThrowsException(): void
    {
        $this->expectException(EmptyMiddlewareChainException::class);

        $this->registerProviders(Providers::DEFAULT_STACK);
        $this->container->set(DispatcherInterface::class, FakeResponderDispatcher::class);

        $this->get('/no-responder-will-handle-this');
    }
}
