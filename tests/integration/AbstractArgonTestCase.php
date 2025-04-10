<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Providers\AppServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Http\Message\Uri;
use Maduser\Argon\Prophecy\ServiceProviders\ArgonHttpFoundation;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AbstractArgonTestCase extends TestCase
{
    protected ArgonContainer $container;

    /**
     * @throws NotFoundException
     * @throws ContainerException
     */
    protected function setUp(): void
    {
        $this->container = new ArgonContainer();
        $this->container->register(ArgonHttpFoundation::class);
        $this->container->register(AppServiceProvider::class);
        $this->container->boot();
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    protected function makeRequest(string $method, string $uri): ResponseInterface
    {
        $request = $this->container->get(ServerRequestInterface::class);
        $request = $request->withMethod($method)->withUri(new Uri($uri));

        $handler = $this->container->get(RequestHandlerInterface::class);

        return $handler->handle($request);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    protected function get(string $uri): ResponseInterface
    {
        return $this->makeRequest('GET', $uri);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    protected function getJson(string $uri): array
    {
        $response = $this->get($uri);
        $this->assertSame(200, $response->getStatusCode(), "Expected 200 OK for [$uri]");
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));

        $data = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($data);

        return $data;
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    protected function getString(string $uri): string
    {
        $response = $this->get($uri);
        $this->assertSame(200, $response->getStatusCode(), "Expected 200 OK for [$uri]");
        $this->assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));

        return (string) $response->getBody();
    }

    protected function assertStatus(ResponseInterface $response, int $expected): void
    {
        $this->assertSame($expected, $response->getStatusCode(), "Expected status code $expected");
    }
}
