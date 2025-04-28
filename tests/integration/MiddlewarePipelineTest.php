<?php

declare(strict_types=1);

namespace Tests\Integration;

use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Http\Server\Factory\RequestHandlerFactory;
use Maduser\Argon\Prophecy\Support\Tag;
use RuntimeException;
use stdClass;
use Tests\Application\Mocks\Providers;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MiddlewarePipelineTest extends AbstractArgonTestCase
{
    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function testDispatcherReturnsExpectedHtml(): void
    {
        $this->registerProviders(Providers::DEFAULT_STACK);

        $response = $this->get('/');

        $this->assertStatus($response, 200);
        $this->assertStringContainsString(
            '<title>Argon Prophecy – Getting Started</title>',
            (string) $response->getBody()
        );
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function testThrowsWhenMiddlewareDoesNotImplementMiddlewareInterface(): void
    {
        $this->container->set(stdClass::class)->tag([Tag::MIDDLEWARE_HTTP => [
            'priority' => 0, 'group' => ['foobar'],
        ]]);

        $factory = new RequestHandlerFactory($this->container);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Service '" . stdClass::class . "' must implement MiddlewareInterface.");

        $factory->create();
    }
}
