[![PHP](https://img.shields.io/badge/php-8.2+-blue)](https://www.php.net/)
[![Build](https://github.com/judus/argon-prophecy/actions/workflows/php.yml/badge.svg)](https://github.com/judus/argon-prophecy/actions)
[![codecov](https://codecov.io/gh/judus/argon-prophecy/branch/master/graph/badge.svg)](https://codecov.io/gh/judus/argon-prophecy)
[![Psalm Level](https://shepherd.dev/github/judus/argon-prophecy/coverage.svg)](https://shepherd.dev/github/judus/argon-prophecy)
[![Code Style](https://img.shields.io/badge/code%20style-PSR--12-brightgreen.svg)](https://www.php-fig.org/psr/psr-12/)
[![Latest Version](https://img.shields.io/packagist/v/maduser/argon-prophecy.svg)](https://packagist.org/packages/maduser/argon-prophecy)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

# Argon Prophecy

A flexible, PSR-compliant HTTP foundation — fully customizable, container-driven, and designed to stay out of your way. 
It is  a foundation to help you build your own framework — without vendor bloat or ecosystem traps.

Built on top of the [Argon](https://github.com/judus/argon) container

*(Documentation work in progress)*

---

## Core Principles

- **100% Dependency Injection:** No hidden macros, no duct tape workarounds, no weirdos
- **Strict Standards:** Full PSR compliance (PSR-7, PSR-15, PSR-17, PSR-18).
- **Minimal Core:** Only essential services are bound by default - to satisfy 1 simple request cycle.
- **High Resilience:** Always emits a valid PSR-7 response, even if you break the error handler.
- **No Lock-In:** There are no classes to extend, no convenience functions, nothing that ties you to Argon.
- **Features?** As many as you want. Cherry-pick your favorite libs and register them to the container. And the best part: they'll get compiled in the container for ZeFastestAF™.


## The Default Setup

Argon provides replaceable default implementations:

- PSR-7/17 `Messages`
- A PSR-15 `RequestHandlerInterface`&#x20;
- A PSR-3 `LoggerInterface` (`NullLogger` or `Monolog` if available)
- A `ResponseEmitterInterface`
- An `ErrorHandlerInterface`
- A HTTP-Kernel

You can override single implementations, or completely replace everything.

---

## Quickstart Example

### Default Setup

Put this in your `index.php` and you're good to go, fully PSR compliant:

```php
use Maduser\Argon\Prophecy\Argon;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Logging\LoggerServiceProvider;
use Maduser\Argon\Prophecy\Provider\ArgonHttpFoundation;

Argon::prophecy(function(ArgonContainer $container) {
    $container->register(LoggerServiceProvider::class);
    $container->register(ArgonHttpFoundation::class);
});
```

---

### Extending the Default Stack

```php
use Maduser\Argon\Prophecy\Argon;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Logging\LoggerServiceProvider;
use Maduser\Argon\Prophecy\Provider\ArgonHttpFoundation;
use YourApp\AppServiceProvider;

Argon::prophecy(function(ArgonContainer $container) {
    $container->register(LoggerServiceProvider::class);
    $container->register(ArgonHttpFoundation::class);
    $container->register(AppServiceProvider::class);
});
```

Override or extend bindings inside your `AppServiceProvider`.

---

### Or Start Completely From Scratch (really?)

In that case you'll have an empty container, not even a Kernel, just a wrapper for the container compilation, good luck!

```php
Argon::prophecy(function(ArgonContainer $container) {
    $container->register(IKnowWhatImDoingServiceProvider::class);
});
```

---

## Where You Come In

Running the default stack will show a placeholder HTML page suggesting the next step:

To define how your app handles requests, bind your own dispatcher to:

```php
$container->set(DispatcherInterface::class, YourDispatcher::class);
```

This will replace the useless built in Dispatcher with your own. From there, it's up to you:

- Wire controllers, services, handlers, etc.
- Integrate or build your Router.
- Execute whatever logic your app needs.

The principle applies to all components. Just override the binding.

---

## Environment Configuration

These settings are **suggested defaults** used by the provided stack:

| Variable                | Default      | Purpose                                          |
| ----------------------- | ------------ | ------------------------------------------------ |
| `APP_DEBUG`             | `false`      | Enables detailed internal error responses        |
| `APP_ENV`               | `production` | Controls behavior like `shouldExit` during tests |
| `APP_COMPILE_CONTAINER` | `false`      | Enables container compilation for ZeFastestAF™   |

These values are injected into the container parameters manually:

```php
$container->getParameters()->set('kernel.debug', $debug);
$container->getParameters()->set('kernel.shouldExit', $shouldExit);
```

No dependency on dotenv or specific loaders. Populate parameters however you want.

---

## Optional Components

(Work in progress. Components are not published yet.)

Available via Composer. Registered manually via ServiceProviders.

- **Advanced Middleware Pipeline + Router:** Per-route middleware pipelines, cached.
- **Views:** Abstraction layer supporting multiple template engines (Plates, Twig, etc.)
- **Console Kernel:** For CLI applications&#x20;
- **Queue**: Because I am bored...

---

## License

MIT License
<!--
Argon is free and open-source. If you use it commercially or benefit from it in your work, please consider sponsoring or contributing back to support continued development.
-->
