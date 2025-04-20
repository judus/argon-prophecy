<?php

declare(strict_types=1);

namespace Tests\Integration\Controllers;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestClassInterface;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Tests\Integration\AbstractArgonTestCase;

class HomeControllerTest extends AbstractArgonTestCase
{
    protected ArgonContainer $container;

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function testIndexReturnsValidJson(): void
    {
        $controller = $this->container->get(HomeController::class);
        $data = $controller->index()->jsonSerialize();

        $this->assertIsArray($data);
        $this->assertSame('Argon Prophecy', $data['name']);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+/', (string) $data['version']);
        $this->assertArrayHasKey('current_time', $data);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function testInjectedAndParamsCallDirectly(): void
    {
        $controller = $this->container->get(HomeController::class);
        $test = $this->container->get(TestClassInterface::class);

        $result = $controller->injectedAndParams($test, '42');

        $this->assertSame('42', $result['id']);
        $this->assertStringContainsString('test string', (string) $result['from_test_class']);
    }

    public function testInjectedAndParamsViaHttp(): void
    {
        $data = $this->getJson('/demo/injected/42');

        $this->assertSame('42', $data['id']);
        $this->assertStringContainsString('test string', (string) $data['from_test_class']);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function testInjectedDependencyEndpoint(): void
    {
        $data = $this->getJson('/demo/injected');

        $this->assertArrayHasKey('result', $data);
        $this->assertStringContainsString('test string', (string) $data['result']);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function testPlainStringResponse(): void
    {
        $string = $this->getString('/demo/plain');

        $this->assertStringContainsString('Just a plain string', $string);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function testResponseObjectReturnsCorrectResponse(): void
    {
        $response = $this->get('/demo/response/object');

        $this->assertStatus($response, 200);
        $this->assertStringContainsString('text/plain', $response->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('Steve is a nerd', (string) $response->getBody());
    }
}
