<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy;

use Closure;
use Maduser\Argon\Prophecy\Contracts\ApplicationInterface;
use Maduser\Argon\Prophecy\Exceptions\ProphecyException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @psalm-api
 */
final class Argon
{
    /**
     * @var list<string>
     */
    private const TRUE_COMPILE_FLAG_VALUES = ['1', 'true', 'on', 'yes'];

    /**
     * @var list<string>
     */
    private const FALSE_COMPILE_FLAG_VALUES = ['0', 'false', 'off', 'no'];

    private static ?Application $app = null;

    public static function check(): ApplicationInterface
    {
        if (self::$app === null) {
            throw ProphecyException::applicationNotBooted();
        }

        return self::$app;
    }

    public static function boot(Closure $callback, string|bool|null $shouldCompile = null): void
    {
        if (self::$app !== null) {
            throw ProphecyException::applicationAlreadyBooted();
        }

        $shouldCompile = self::resolveCompileFlag($shouldCompile ?? $_ENV['APP_COMPILE_CONTAINER'] ?? null);

        $compileConfig = $shouldCompile ? self::resolveCompileConfig() : null;

        self::$app = (new Application())->register($callback);

        if ($compileConfig !== null) {
            self::$app->compile(
                $compileConfig['filePath'],
                $compileConfig['className'],
                $compileConfig['namespace']
            );
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
        self::$app?->reset();
        self::$app = null;
    }

    private static function resolveCompileFlag(string|bool|null $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null || trim($value) === '') {
            return false;
        }

        $normalized = strtolower(trim($value));

        if (in_array($normalized, self::TRUE_COMPILE_FLAG_VALUES, true)) {
            return true;
        }

        if (in_array($normalized, self::FALSE_COMPILE_FLAG_VALUES, true)) {
            return false;
        }

        throw ProphecyException::invalidCompileFlag($value);
    }

    /**
     * @return array{filePath: string, className: string, namespace: string}
     */
    private static function resolveCompileConfig(): array
    {
        $filePath = self::envString('APP_COMPILE_FILE_NAME');
        $className = self::envString('APP_COMPILE_CLASS_NAME');
        $namespace = self::envString('APP_COMPILE_CLASS_NAMESPACE') ?? '';

        if ($filePath === null || $className === null) {
            $missing = [];
            if ($filePath === null) {
                $missing[] = 'APP_COMPILE_FILE_NAME';
            }

            if ($className === null) {
                $missing[] = 'APP_COMPILE_CLASS_NAME';
            }

            throw ProphecyException::incompleteCompileConfiguration($missing);
        }

        return [
            'filePath' => $filePath,
            'className' => $className,
            'namespace' => $namespace,
        ];
    }

    private static function envString(string $name): ?string
    {
        $value = $_ENV[$name] ?? null;

        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return $value;
    }
}
