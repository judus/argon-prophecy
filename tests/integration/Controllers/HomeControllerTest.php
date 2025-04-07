<?php

declare(strict_types=1);

namespace Tests\Integration\Controllers;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestClassInterface;
use Tests\Integration\AbstractArgonTestCase;

class HomeControllerTest extends AbstractArgonTestCase
{
    public function testIndexReturnsValidJson(): void
    {
        $controller = $this->container->get(HomeController::class);
        $data = $controller->index()->jsonSerialize();

        $this->assertIsArray($data);
        $this->assertSame('Argon Prophecy', $data['name']);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+/', $data['version']);
        $this->assertArrayHasKey('current_time', $data);
    }

    public function testInjectedAndParamsCallDirectly(): void
    {
        $controller = $this->container->get(HomeController::class);
        $test = $this->container->get(TestClassInterface::class);

        $result = $controller->injectedAndParams($test, '42');

        $this->assertSame('42', $result['id']);
        $this->assertStringContainsString('test string', $result['from_test_class']);
    }

    public function testInjectedAndParamsViaHttp(): void
    {
        $data = $this->getJson('/demo/injected/42');

        $this->assertSame('42', $data['id']);
        $this->assertStringContainsString('test string', $data['from_test_class']);
    }

    public function testInjectedDependencyEndpoint(): void
    {
        $data = $this->getJson('/demo/injected');

        $this->assertArrayHasKey('result', $data);
        $this->assertStringContainsString('test string', $data['result']);
    }

    public function testPlainStringResponse(): void
    {
        $html = $this->getString('/demo/plain');

        $this->assertStringContainsString('Just a plain string', $html);
    }
}
