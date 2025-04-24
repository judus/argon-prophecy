<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Support;

use ReflectionClass;

class Tag
{
    public const PSR17_FACTORY = 'psr-17';
    public const PSR7_MESSAGE = 'psr-7';
    public const PSR15 = 'psr-15';
    public const HTTP = 'http';
    public const KERNEL = 'kernel.http';
    public const MIDDLEWARE_HTTP = 'middleware.http';
    public const MIDDLEWARE_PIPELINE = 'middleware.pipeline';
    public const REQUEST_HANDLER_FACTORY = 'request_handler_factory';
    public const EXCEPTION_HANDLER = 'exception.handler';
    public const EXCEPTION_DISPATCHER = 'exception.dispatcher';
    public const EXCEPTION_FORMATTER = 'exception.formatter';
    public const DISPATCHER = 'middleware.http.dispatcher';

    public static function all(): array
    {
        return (new ReflectionClass(self::class))->getConstants();
    }
}
