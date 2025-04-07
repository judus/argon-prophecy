<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\TestClassInterface;
use JsonSerializable;
use RuntimeException;

readonly class HomeController
{
    public function index(): JsonSerializable
    {
        return new class implements JsonSerializable {
            public function jsonSerialize(): array
            {
                return [
                    'name' => 'Argon Prophecy',
                    'version' => '1.0.0-beta.6b',
                    'description' => 'A strict, DI-first and PSR-compliant PHP runtime for law-abiding Prophets.',
                    'current_time' => date('Y-m-d H:i:s'),
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
