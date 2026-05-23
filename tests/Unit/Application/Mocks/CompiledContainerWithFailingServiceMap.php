<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Mocks;

use Maduser\Argon\Container\ArgonContainer;
use RuntimeException;

final class CompiledContainerWithFailingServiceMap extends ArgonContainer
{
    /**
     * @return array<string, mixed>
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getServiceMap(): array
    {
        throw new RuntimeException('service map unavailable');
    }
}
