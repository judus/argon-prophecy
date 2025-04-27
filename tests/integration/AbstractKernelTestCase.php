<?php

declare(strict_types=1);

namespace Tests\Integration;

use Maduser\Argon\Container\Contracts\ServiceProviderInterface;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Prophecy\Argon;
use Maduser\Argon\Container\ArgonContainer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Maduser\Argon\Http\Message\Uri;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

abstract class AbstractKernelTestCase extends TestCase
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
     * @param class-string<ServiceProviderInterface>|list<class-string<ServiceProviderInterface>> $providers
     */
    protected function bootApplication(string|array $providers): void
    {
        Argon::boot(function (ArgonContainer $container) use ($providers): void {
            $container->register($providers);
        }, shouldCompile: false);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    protected function makeKernelRequest(string $method, string $uri): void
    {
        $request = $this->container->get(ServerRequestInterface::class);
        $request = $request->withMethod($method)->withUri(new Uri($uri));

        $kernel = $this->container->get(\Maduser\Argon\Contracts\KernelInterface::class);

        ob_start();
        $kernel->handle();
        ob_end_clean();
    }
}
