<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Maduser\Argon\Prophecy\Support\Tag;
use PHPUnit\Framework\TestCase;

final class TagTest extends TestCase
{
    public function testAllReturnsNamedTagMap(): void
    {
        self::assertSame([
            'PSR17_FACTORY' => Tag::PSR17_FACTORY,
            'PSR7_MESSAGE' => Tag::PSR7_MESSAGE,
            'PSR15' => Tag::PSR15,
            'HTTP' => Tag::HTTP,
            'KERNEL' => Tag::KERNEL,
            'MIDDLEWARE_HTTP' => Tag::MIDDLEWARE_HTTP,
            'MIDDLEWARE_PIPELINE' => Tag::MIDDLEWARE_PIPELINE,
            'REQUEST_HANDLER_FACTORY' => Tag::REQUEST_HANDLER_FACTORY,
            'EXCEPTION_HANDLER' => Tag::EXCEPTION_HANDLER,
            'EXCEPTION_DISPATCHER' => Tag::EXCEPTION_DISPATCHER,
            'EXCEPTION_FORMATTER' => Tag::EXCEPTION_FORMATTER,
            'DISPATCHER' => Tag::DISPATCHER,
            'CONSOLE_COMMAND' => Tag::CONSOLE_COMMAND,
        ], Tag::all());
    }
}
