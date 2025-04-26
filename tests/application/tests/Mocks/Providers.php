<?php

declare(strict_types=1);

namespace Tests\Application\Mocks;

use Maduser\Argon\Logging\LoggerServiceProvider;
use Maduser\Argon\Prophecy\Provider\ArgonHttpFoundation;
use Tests\Application\Mocks\Providers\UserExtendsDefaultStack;

final readonly class Providers
{
    public const DEFAULT_STACK = [
        LoggerServiceProvider::class,
        ArgonHttpFoundation::class
    ];

    public const NO_LOGGER_STACK = [
        ArgonHttpFoundation::class
    ];

    public const USER_EXTENDS_DEFAULT_STACK = [
        LoggerServiceProvider::class,
        ArgonHttpFoundation::class,
        UserExtendsDefaultStack::class
    ];
}
