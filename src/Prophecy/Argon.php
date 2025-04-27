<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy;

use Closure;
use Maduser\Argon\Prophecy\Contracts\ApplicationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class Argon
{
    private static ?Application $app = null;

    public static function check(): ApplicationInterface
    {
        if (self::$app === null) {
            throw new RuntimeException('Application not booted yet.');
        }

        return self::$app;
    }

    public static function boot(Closure $callback, string|bool|null $shouldCompile = null): void
    {
        if (self::$app !== null) {
            throw new RuntimeException('Application already booted.');
        }

        self::$app = (new Application())->register($callback);

        $shouldCompile = filter_var(
            $shouldCompile ?? $_ENV['APP_COMPILE_CONTAINER'] ?? false,
            FILTER_VALIDATE_BOOL
        );

        if ($shouldCompile) {
            $filePath = $_ENV['APP_COMPILE_FILE_NAME'];
            $className = $_ENV['APP_COMPILE_CLASS_NAME'];
            $namespace = $_ENV['APP_COMPILE_CLASS_NAMESPACE'];

            self::$app->compile($filePath, $className, $namespace);
        }
    }

    public static function handle(?ServerRequestInterface $request = null): void
    {
        self::check()->handle($request);
    }

    public static function process(?ServerRequestInterface $request = null): ResponseInterface
    {
        return self::check()->process($request);
    }

    public static function emit(ResponseInterface $response): void
    {
        self::check()->emit($response);
    }

    public static function prophecy(Closure $callback, string|bool|null $shouldCompile = null): void
    {
        self::boot($callback, $shouldCompile);
        self::handle();
    }

    public static function reset(): void
    {
        self::$app = null;
    }
}
