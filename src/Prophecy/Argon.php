<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy;

use Closure;
use Throwable;

final class Argon
{
    public static function prophecy(Closure $callback, string $shouldCompile): void
    {
        self::boot($callback, $shouldCompile);
    }

    public static function boot(
        Closure $callback,
        string $shouldCompile
    ): void {
        try {
            $app = (new Application())->register($callback);

            $shouldCompile = filter_var($_ENV['APP_COMPILE_CONTAINER'] ?? false, FILTER_VALIDATE_BOOL);

            if ($shouldCompile) {
                $filePath = $_ENV['APP_COMPILE_FILE_NAME'];
                $className = $_ENV['APP_COMPILE_CLASS_NAME'];
                $namespace = $_ENV['APP_COMPILE_CLASS_NAMESPACE'];
                $app->compile($filePath, $className, $namespace);
            }

            $app->handle();
        } catch (Throwable $e) {
            echo sprintf(
                "<pre>An unexpected error occurred: \n\n%s \n\nIn File: %s:%d\n\n%s</pre>",
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            );
        }
    }
}
