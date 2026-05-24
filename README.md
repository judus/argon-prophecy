# Argon Prophecy

[![PHP](https://img.shields.io/badge/php-8.2+-blue)](https://www.php.net/)
[![Build](https://github.com/judus/argon-prophecy/actions/workflows/php.yml/badge.svg)](https://github.com/judus/argon-prophecy/actions)
[![codecov](https://codecov.io/gh/judus/argon-prophecy/branch/master/graph/badge.svg)](https://codecov.io/gh/judus/argon-prophecy)
[![Psalm Level](https://shepherd.dev/github/judus/argon-prophecy/coverage.svg)](https://shepherd.dev/github/judus/argon-prophecy)
[![Latest Version](https://img.shields.io/packagist/v/maduser/argon-prophecy.svg)](https://packagist.org/packages/maduser/argon-prophecy)
[![Downloads](https://img.shields.io/packagist/dt/maduser/argon-prophecy.svg)](https://packagist.org/packages/maduser/argon-prophecy)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

`maduser/argon-prophecy` is the small runtime switchboard for Argon
applications. It boots an `ArgonContainer`, runs the registered service
providers, resolves the active application handler, and delegates execution to
the HTTP or CLI kernel that the application registered.

## Installation

```bash
composer require maduser/argon-prophecy
```

## Runtime Entry Point

```php
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Prophecy\Argon;

Argon::prophecy(static function (ArgonContainer $container): void {
    $container->register(AppServiceProvider::class);
});
```

For tests or advanced runtimes, `Argon::boot()`, `Argon::process()`,
`Argon::emit()`, and `Argon::reset()` expose the lifecycle in smaller steps.

## Container Compilation

Prophecy can load or generate a compiled container when compilation is enabled.
The compile file path, class name, and namespace are explicit runtime
configuration, not guessed paths.

## Scope

Prophecy does not define routes, middleware, message factories, exception
formatters, or console commands. It coordinates the packages that provide those
pieces.

## Quality Gate

```bash
composer check
```
