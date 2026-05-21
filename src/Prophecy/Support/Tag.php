<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Support;

/**
 * @psalm-api
 */
final class Tag
{
    public const string PSR17_FACTORY = 'psr-17';
    public const string PSR7_MESSAGE = 'psr-7';
    public const string PSR15 = 'psr-15';
    public const string HTTP = 'http';
    public const string KERNEL = 'kernel.http';
    public const string MIDDLEWARE_HTTP = 'middleware.http';
    public const string MIDDLEWARE_PIPELINE = 'middleware.pipeline';
    public const string REQUEST_HANDLER_FACTORY = 'request_handler_factory';
    public const string EXCEPTION_HANDLER = 'exception.handler';
    public const string EXCEPTION_DISPATCHER = 'exception.dispatcher';
    public const string EXCEPTION_FORMATTER = 'exception.formatter';
    public const string DISPATCHER = 'middleware.http.dispatcher';
    public const string CONSOLE_COMMAND = 'cli.command';

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'PSR17_FACTORY' => self::PSR17_FACTORY,
            'PSR7_MESSAGE' => self::PSR7_MESSAGE,
            'PSR15' => self::PSR15,
            'HTTP' => self::HTTP,
            'KERNEL' => self::KERNEL,
            'MIDDLEWARE_HTTP' => self::MIDDLEWARE_HTTP,
            'MIDDLEWARE_PIPELINE' => self::MIDDLEWARE_PIPELINE,
            'REQUEST_HANDLER_FACTORY' => self::REQUEST_HANDLER_FACTORY,
            'EXCEPTION_HANDLER' => self::EXCEPTION_HANDLER,
            'EXCEPTION_DISPATCHER' => self::EXCEPTION_DISPATCHER,
            'EXCEPTION_FORMATTER' => self::EXCEPTION_FORMATTER,
            'DISPATCHER' => self::DISPATCHER,
            'CONSOLE_COMMAND' => self::CONSOLE_COMMAND,
        ];
    }
}
