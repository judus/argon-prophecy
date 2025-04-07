<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\TestClassInterface;
use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

readonly class HomeController
{
    public function __construct(private ServerRequestInterface $request)
    {
    }

    public function index(): JsonSerializable
    {
        $request = $this->request;

        return new class($request) implements JsonSerializable {
            public function __construct(private ServerRequestInterface $request) {}

            public function jsonSerialize(): array
            {
                $uri = $this->request->getUri();
                $baseUrl = rtrim($uri->getScheme() . '://' . $uri->getHost() . ':' . (string )$uri->getPort(), '/');

                return [
                    'name' => 'Argon Prophecy',
                    'version' => '0.0.1-dev',
                    'container' => '1.0.0-beta.7',
                    'description' => 'A strict, DI-First and PSR-compliant PHP runtime for law-abiding Prophets.',
                    'current_time' => date('Y-m-d H:i:s'),
                    'routes' => [
                        'root' => "$baseUrl/",
                        'di only' => "$baseUrl/demo/injected",
                        'di + param' => "$baseUrl/demo/injected/123",
                        'two params' => "$baseUrl/demo/params/456/important",
                        'html/plain' => "$baseUrl/demo/plain",
                        'throws exception' => "$baseUrl/demo/error",
                    ],
                ];
            }
        };
    }

    public function onlyParams(string $id, string $cat): array
    {
        return [
            'id' => $id,
            'category' => $cat,
        ];
    }

    public function injectedDependency(TestClassInterface $test): array
    {
        return [
            'result' => $test->test(),
        ];
    }

    public function injectedAndParams(TestClassInterface $test, string $id): array
    {
        return [
            'id' => $id,
            'from_test_class' => $test->test(),
        ];
    }

    public function throws(): void
    {
        throw new RuntimeException('This is a test exception');
    }

    public function stringResponse(): string
    {
        return 'Just a plain string response';
    }
}
