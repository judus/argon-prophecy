<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Mocks;

use Maduser\Argon\Container\ArgonContainer;

final class CompiledContainerWithServiceMap extends ArgonContainer
{
    /**
     * @return array<string, string>
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getServiceMap(): array
    {
        return [
            'app' => 'compiledApp',
            'logger' => 'compiledLogger',
        ];
    }
}
