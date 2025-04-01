<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Controllers;

use JsonSerializable;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Http\Factory\ResponseFactory;
use Maduser\Argon\Http\Message\Response;
use Maduser\Argon\Http\Message\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

readonly class ArgonController
{
    public function __construct(
        private ServerRequestInterface $request,
    ) {
    }

    public function __invoke(): string
    {
        return '<div>Argon Prophecy</div>';
    }

    private function throwException(): void
    {
        throw new RuntimeException('This is a test exception');
    }

    private function getSerializableObject(): JsonSerializable
    {
        return new class implements JsonSerializable {
            public function jsonSerialize(): array
            {
                return [
                    'name' => 'Argon Prophecy',
                    'version' => '1.0.0-beta.5',
                    'description' => 'A PSR-compliant, DI-first PHP runtime.',
                    'current_time' => date('Y-m-d H:i:s'),
                ];
            }
        };
    }
}
