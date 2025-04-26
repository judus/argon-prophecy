<?php

declare(strict_types=1);

namespace Tests\Unit\Prophecy\Support;

use Maduser\Argon\Prophecy\Support\Tag;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    public function testAllReturnsAllConstants(): void
    {
        $expected = [
            'PSR17_FACTORY' => 'psr-17',
            'PSR7_MESSAGE' => 'psr-7',
            'PSR15' => 'psr-15',
            'HTTP' => 'http',
            'KERNEL' => 'kernel.http',
            'MIDDLEWARE_HTTP' => 'middleware.http',
            'MIDDLEWARE_PIPELINE' => 'middleware.pipeline',
            'REQUEST_HANDLER_FACTORY' => 'request_handler_factory',
            'EXCEPTION_HANDLER' => 'exception.handler',
            'EXCEPTION_DISPATCHER' => 'exception.dispatcher',
            'EXCEPTION_FORMATTER' => 'exception.formatter',
            'DISPATCHER' => 'middleware.http.dispatcher',
        ];

        $this->assertSame($expected, Tag::all());
    }
}
