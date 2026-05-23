[![PHP](https://img.shields.io/badge/php-8.2+-blue)](https://www.php.net/)
[![Build](https://github.com/judus/argon-prophecy/actions/workflows/php.yml/badge.svg)](https://github.com/judus/argon-prophecy/actions)
[![codecov](https://codecov.io/gh/judus/argon-prophecy/branch/master/graph/badge.svg)](https://codecov.io/gh/judus/argon-prophecy)
[![Psalm Level](https://shepherd.dev/github/judus/argon-prophecy/coverage.svg)](https://shepherd.dev/github/judus/argon-prophecy)
[![Code Style](https://img.shields.io/badge/code%20style-PSR--12-brightgreen.svg)](https://www.php-fig.org/psr/psr-12/)
[![Latest Version](https://img.shields.io/packagist/v/maduser/argon-prophecy.svg)](https://packagist.org/packages/maduser/argon-prophecy)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

# Argon Prophecy

> Prophecy boots and runs your application. It does not become your application.

Argon Prophecy is a small runtime shell for PHP applications built around
[Argon Container](https://github.com/judus/argon). It gives you one predictable
place to configure the container, resolve an application handler, and run an HTTP
or CLI lifecycle.

Your application remains normal PHP. Use PSR interfaces, your own services, your
own folders, your own architecture. Use cases, transaction scripts, CQRS,
hexagonal boundaries, procedural services: Prophecy does not care.

## What Prophecy Does

- Boots an `ArgonContainer` through a simple callback.
- Resolves one application handler from the container.
- Runs either an HTTP kernel or a CLI kernel.
- Provides bootstrap and runtime error boundaries.
- Keeps logging optional through PSR-3.
- Can load a compiled container when you opt in.
- Exposes a tiny static facade for scripts and front controllers.

## Prophecy And The Argon Suite

Prophecy is the runtime package. It keeps the application entry point small and
delegates the real work to the handler you bind.

The wider Argon suite can still be composed into a framework-style stack with
routing, middleware, console, database, and view packages. You can also skip
those packages and wire your own stack instead.

Examples and how-tos:

- [Composing the Argon suite](https://github.com/judus/argon-prophecy/wiki/Composing-the-Argon-suite)
- [Registering application integrations](https://github.com/judus/argon-prophecy/wiki/Registering-application-integrations)

## Installation

```bash
composer require maduser/argon-prophecy
```

## Quickstart

The shortest useful entry point is the facade:

```php
<?php

declare(strict_types=1);

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Prophecy\Argon;

require __DIR__ . '/../vendor/autoload.php';

Argon::prophecy(static function (ArgonContainer $container): void {
    $container->register(AppServiceProvider::class);
});
```

That is the intended boundary. The callback wires your app. Prophecy then boots
the container, resolves the app handler, and gets out of the way.

## Application Handler

Prophecy runs whatever handler you bind. Register one of these container ids:

- `Maduser\Argon\Contracts\Handler\AppHandlerInterface`
- `Maduser\Argon\Contracts\Handler\HttpKernelInterface`
- `Maduser\Argon\Contracts\Handler\CliKernelInterface`

Register exactly one lifecycle handler. If both HTTP and CLI handlers are bound,
also bind `AppHandlerInterface` explicitly so Prophecy knows which handler owns
the current entry point. Invalid handler bindings fail during bootstrap.

`AppHandlerInterface` is the common lifecycle:

```php
interface AppHandlerInterface
{
    public function run(): int;

    public function terminate(int $code = 0, bool $shouldExit = true): void;
}
```

For a CLI app, bind a CLI kernel:

```php
<?php

declare(strict_types=1);

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Contracts\Handler\CliKernelInterface;

final class AppServiceProvider extends AbstractServiceProvider
{
    public function register(ArgonContainer $container): void
    {
        $container->set(CliKernelInterface::class, CliKernel::class);
    }
}
```

For an HTTP app, bind an HTTP kernel:

```php
<?php

declare(strict_types=1);

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Contracts\Handler\HttpKernelInterface;

final class AppServiceProvider extends AbstractServiceProvider
{
    public function register(ArgonContainer $container): void
    {
        $container->set(HttpKernelInterface::class, HttpKernel::class);
    }
}
```

An HTTP kernel also implements `process()` and `emit()`:

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpKernelInterface extends AppHandlerInterface
{
    public function handle(?ServerRequestInterface $request = null): void;

    public function process(?ServerRequestInterface $request = null): ResponseInterface;

    public function emit(ResponseInterface $response): void;
}
```

## HTTP Control

`Argon::prophecy()` is convenient for a classic request-per-process front
controller. If you need more control, boot once and call the HTTP methods
explicitly:

```php
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Prophecy\Argon;

Argon::boot(static function (ArgonContainer $container): void {
    $container->register(AppServiceProvider::class);
});

$response = Argon::process($request);

Argon::emit($response);
```

This is the shape long-running adapters such as Swoole should use: translate the
runtime request to PSR-7, call `process()`, and emit the response through the
runtime.

## Service Providers

Service providers keep the entry point small while letting your application own
its structure:

```php
<?php

declare(strict_types=1);

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Psr\Log\LoggerInterface;

final class AppServiceProvider extends AbstractServiceProvider
{
    public function register(ArgonContainer $container): void
    {
        $container->register(InfrastructureProvider::class);
        $container->register(ApplicationProvider::class);

        $container->set(LoggerInterface::class, AppLogger::class);
    }

    public function boot(ArgonContainer $container): void
    {
        // Optional post-registration setup.
    }
}
```

Use many providers, one provider, or none. Prophecy only needs the container to
resolve an application handler before the lifecycle starts.

## Logging

`LoggerServiceProvider` binds `Psr\Log\LoggerInterface`. If Monolog is installed,
it creates a Monolog logger. If Monolog is not installed, it falls back to
`Psr\Log\NullLogger`.

```php
use Maduser\Argon\Logging\LoggerServiceProvider;

$container->register(LoggerServiceProvider::class);
```

Logging is optional. You can also bind your own PSR-3 logger directly.

## Error Handling

Prophecy registers a bootstrap error handler while the application is starting.
Once the container is available, it will use a bound
`Maduser\Argon\Support\Contracts\ErrorHandlerInterface` for HTTP runtime
exceptions when one is registered.

If no runtime error handler is bound, Prophecy falls back to the bootstrap error
handler. If a runtime error handler is bound but cannot be resolved or
registered, Prophecy treats that as an application configuration error and fails
during bootstrap.

## Container Compilation

Compilation is opt-in. Pass `true` or set `APP_COMPILE_CONTAINER=true`, then
provide the generated container location and class:

`APP_COMPILE_CONTAINER` accepts `true`, `false`, `1`, `0`, `yes`, `no`, `on`,
and `off`. Empty or unset values disable compilation. Any other non-empty value
fails during boot.

```dotenv
APP_COMPILE_CONTAINER=true
APP_COMPILE_FILE_NAME=var/cache/CompiledContainer.php
APP_COMPILE_CLASS_NAME=CompiledContainer
APP_COMPILE_CLASS_NAMESPACE=App\\Cache
```

```php
Argon::prophecy(
    static function (ArgonContainer $container): void {
        $container->register(AppServiceProvider::class);
    },
    shouldCompile: true
);
```

The container still belongs to your app. Prophecy only decides whether to build
or load the compiled version.

## Facade API

| Method       | Description                                                           |
|--------------|-----------------------------------------------------------------------|
| `boot()`     | Configures the container and prepares the application without running. |
| `handle()`   | Runs the active handler. Emits HTTP responses through the HTTP kernel. |
| `process()`  | Processes an HTTP request and returns a PSR-7 response.                |
| `emit()`     | Emits a PSR-7 response through the active HTTP kernel.                 |
| `prophecy()` | Calls `boot()` and then `handle()`.                                    |
| `check()`    | Returns the booted application instance.                               |
| `reset()`    | Clears facade state and unregisters bootstrap handlers.                |

## License

MIT License. Free to use, extend, and adapt.
