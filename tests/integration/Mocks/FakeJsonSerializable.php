<?php

declare(strict_types=1);

namespace Tests\Integration\Mocks;

use JsonSerializable;

final class FakeJsonSerializable implements JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [
            'mocked' => true,
            'time' => time(),
        ];
    }
}
