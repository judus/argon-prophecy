<?php

declare(strict_types=1);

namespace Tests\Integration;

use Maduser\Argon\Container\Contracts\ServiceProviderInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Http\Message\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

abstract class AbstractArgonTestCase extends TestCase
{
    protected ArgonContainer $container;

    protected function setUp(): void
    {
        $this->container = new ArgonContainer();

        $basePath = realpath(dirname(__DIR__) . '/application');
        if ($basePath === false) {
            throw new RuntimeException('Could not resolve basePath for integration tests.');
        }
        $this->container->getParameters()->set('basePath', $basePath);
    }

    /**
     * @param list<class-string> $providers
     *
     * @throws ContainerException
     * @throws NotFoundException
     */
    protected function registerProviders(array $providers): void
    {
        foreach ($providers as $provider) {
            if (!class_exists($provider)) {
                throw new RuntimeException("Service provider [$provider] does not exist.");
            }

            if (!is_subclass_of($provider, ServiceProviderInterface::class)) {
                throw new RuntimeException(
                    "Service provider [$provider] must implement " . ServiceProviderInterface::class
                );
            }

            $this->container->register($provider);
        }
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    protected function makeRequest(string $method, string $uri): ResponseInterface
    {
        $this->container->boot();

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
        $this->assertStringContainsString('text/plain', $response->getHeaderLine('Content-Type'));

        return (string) $response->getBody();
    }

    protected function assertStatus(ResponseInterface $response, int $expected): void
    {
        $this->assertSame($expected, $response->getStatusCode(), "Expected status code $expected");
    }
}
