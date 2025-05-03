[![PHP](https://img.shields.io/badge/php-8.2+-blue)](https://www.php.net/)
[![Build](https://github.com/judus/argon-prophecy/actions/workflows/php.yml/badge.svg)](https://github.com/judus/argon-prophecy/actions)
[![codecov](https://codecov.io/gh/judus/argon-prophecy/branch/master/graph/badge.svg)](https://codecov.io/gh/judus/argon-prophecy)
[![Psalm Level](https://shepherd.dev/github/judus/argon-prophecy/coverage.svg)](https://shepherd.dev/github/judus/argon-prophecy)
[![Code Style](https://img.shields.io/badge/code%20style-PSR--12-brightgreen.svg)](https://www.php-fig.org/psr/psr-12/)
[![Latest Version](https://img.shields.io/packagist/v/maduser/argon-prophecy.svg)](https://packagist.org/packages/maduser/argon-prophecy)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

# Argon Prophecy

> A compiled, minimal PHP framework foundation — designed for when speed, transparency, and running costs matter.

---

**Core Features:**

- **Compiled container**: No reflection, no proxies.
- **Strict PSR compliance**: PSR-3, PSR-7, PSR-11, PSR-15, PSR-17.
- **High Resilience:** Always emits a valid PSR-7 response.
- **No framework leakage**: Your domain logic stays clean.
- **Minimal by default**: Pull in only what you need.
- **Learn it in minutes**: Less than ten methods to remember.

**Default Stack includes:**

- PSR-7 / PSR-17 HTTP Messages
- PSR-11 Dependency Injection Container
- PSR-15 Middleware Pipeline
- PSR-3 Logger
- Response Emitter and Error Handler
- Built-in Responder Middleware (JSON, HTML, Plain Text, PSR-7 — see below)
- Minimal Kernel to orchestrate request/response lifecycle
- Application manager and static facade to simplify booting

_All components are modular and replaceable._

---

## Installation

```bash
composer require maduser/argon-prophecy
```

---

## Minimal Learning Curve

You can be productive with Argon within minutes if you know basic Dependency Injection concepts:

**Argon Container API:**

- `register()`, `boot()`, `set()`, `get()`, `factory()`, `tag()`
- `getParameters()->set()`, `getParameters()->get()`

**Argon Prophecy API:**

- `prophecy()`, `process()`

Further API documentation is available below.\
For now, let the Quickstart Example speak for itself.

---

## Quickstart Example

```php
<?php

declare(strict_types=1);

use Maduser\Argon\Prophecy\Argon;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Logging\LoggerServiceProvider;
use Maduser\Argon\Prophecy\Provider\ArgonHttpFoundation;

Argon::prophecy(function(ArgonContainer $container): void {
    $container->register(LoggerServiceProvider::class);
    $container->register(ArgonHttpFoundation::class);
});
```
When you run this, you'll see a default HTML output from a placeholder Dispatcher middleware.
It suggests the next step: override the binding with your own `Dispatcher`.

To do this, create a ServiceProvider:
```php
<?php

declare(strict_types=1);

namespace YourApp\Provider;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Contracts\ServiceProviderInterface;
use Maduser\Argon\Contracts\Http\Server\DispatcherInterface;
use YourApp\Dispatcher\MyDispatcher;

final readonly class AppServiceProvider implements ServiceProviderInterface
{
    public function register(ArgonContainer $container): void
    {
        // Override the default dispatcher binding with your own
        $container->set(DispatcherInterface::class, MyDispatcher::class)
            // The middleware pipeline factory loads by tag, so we tag it:
            ->tag([Tag::MIDDLEWARE_HTTP => ['priority' => 6000, 'group' => ['api', 'web']]]);
    }

    public function boot(): void
    {
        // boot() allows dynamic setup after the container is loaded.
    }
}
```

You can now register your AppServiceProvider along with the other providers:

```php
Argon::prophecy(function(ArgonContainer $container): void {
    $container->register(LoggerServiceProvider::class);
    $container->register(ArgonHttpFoundation::class);
    $container->register(AppServiceProvider::class);
});
```

It is perfectly fine — if not recommended — to nest your ServiceProviders.

This way your app launcher becomes minimal, and you don't have to change it anymore:
```php
Argon::prophecy(function(ArgonContainer $container): void {
    $container->register(AppServiceProvider::class);
});
```
Inside the `register()` method of your `AppServiceProvider`, you group all your lower-level providers:
```php
public function register(ArgonContainer $container): void
{
    $container->register(LoggerServiceProvider::class);
    $container->register(ArgonHttpFoundation::class);
    $container->register(RouterServiceProvider::class);
    $container->register(EventServiceProvider::class);
}
```

---

## ArgonContainer API

| Method       | Parameters                                       | Returns          | Description                                                                          |
|--------------|--------------------------------------------------|------------------|--------------------------------------------------------------------------------------|
| `register()` | `class-string<ServiceProviderInterface> $class`  | `ArgonContainer` | Registers a service provider.                                                        |
| `set()`      | `string $id`, `\Closure\|string\|null $concrete` | `ArgonContainer` | Registers a service as shared by default. Use `->transient()` to make it non-shared. |
| `get()`      | `string $id`                                     | `object`         | Resolves and returns the service instance.                                           |
| `has()`      | `string $id`                                     | `bool`           | Checks if a service binding exists.                                                  |
| `tag()`      | `string $id`, `list<string> $tags`               | `ArgonContainer` | Tags a service with one or more labels.                                              |

---

## Argon Prophecy API

| Method       | Parameters                                                           | Returns             | Description                                                             | 
|--------------|----------------------------------------------------------------------|---------------------|-------------------------------------------------------------------------|
| `prophecy()` | `Closure(ArgonContainer): void`                                      | `void`              | Boots the application, configures services and handles requests.        |
| `process()`  | `ServerRequestInterface\|null`                                       | `ResponseInterface` | Processes a request without emitting it (useful for testing).           |
| `emit()`     | `ResponseInterface $response`                                        | `void`              | Emits a PSR-7 Response manually (advanced control).                     |
| `boot()`     | `Closure(ArgonContainer): void`, `string\|bool\|null $shouldCompile` | `void`              | Boots the application without handling a request.                       |
| `reset()`    | none                                                                 | `void`              | Resets the internal application state (for tests or multi-run scripts). |

---

## Tip 1: Organizing Your Service Providers

As your architecture grows, you will want to split up and organize your service definitions into manageable chunks.

As mentioned before, it is perfectly valid to nest ServiceProviders inside each other. Additionally, you can create
custom abstract ServiceProvider bases that group your logic more cleanly.

Example:
```php
<?php

declare(strict_types=1);

namespace YourApp\Provider;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Contracts\ServiceProviderInterface;
use Maduser\Argon\Container\Contracts\ParameterStoreInterface;
use YourApp\Contracts\RouterInterface;
use YourApp\Contracts\EventsInterface;

abstract class MyAbstractServiceProvider implements ServiceProviderInterface
{
    public function register(ArgonContainer $container): void
    {
        $this->configure($container->getParameters());
        $this->services($container);
        $this->routes($container->get(RouterInterface::class));
        $this->events($container->get(EventsInterface::class));
    }

    public function boot(): void
    {
        // Optional: dynamic setup after container boot
    }

    protected function configure(ParameterStoreInterface $parameters): void
    {
        // Override to configure container parameters
    }

    protected function services(ArgonContainer $container): void
    {
        // Override to bind services
    }

    protected function routes(RouterInterface $router): void
    {
        // Override to define routes
    }

    protected function events(EventsInterface $events): void
    {
        // Override to register event listeners
    }
}
```



---

## Tip 2: Examples Loading Third-Party Libraries

Eloquent ORM Example:
```php
class EloquentServiceProvider extends AbstractServiceProvider
{
    public function boot(ArgonContainer $container): void
    {
        $capsule = new Manager();

        $capsule->addConnection([
            // Adapt to your liking
            'driver'   => 'sqlite',
            'database' => __DIR__ . '/../../resources/database/database.sqlite',
            'prefix'   => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
```

Twig Template Engine Example
```php
<?php

declare(strict_types=1);

namespace App\Providers;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function register(ArgonContainer $container): void
    {
        $parameters = $container->getParameters();
        $basePath = (string) $parameters->get('basePath', __DIR__ . '/../../');
        $viewsPath = $basePath . '/resources/views';

        $container->set(FilesystemLoader::class, FilesystemLoader::class, [
            'paths' => [$viewsPath],
        ]);

        $container->set(Environment::class, Environment::class, [
            'loader' => FilesystemLoader::class,
            'options' => [
                'cache' => $parameters->get('twig.cache', false),
                'debug' => $parameters->get('twig.debug', false),
            ],
        ]);
    }
}
```
These are just quick examples of the principle. There's room for improvement. Use of factories, dynamic configurations, etc.

---

## License

MIT License — free to use, extend, and adapt.
