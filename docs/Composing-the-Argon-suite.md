# Composing the Argon suite

Prophecy keeps the runtime boundary small:

```php
Argon::prophecy(static function (ArgonContainer $container): void {
    $container->register(AppServiceProvider::class);
});
```

The application provider decides what kind of stack the application wants. That
can be the Argon packages, your own packages, or a mix of both.

## HTTP stack

An HTTP application can compose routing, middleware, and view support by
registering the relevant package providers:

```php
<?php

declare(strict_types=1);

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Middleware\Provider\MiddlewaresServiceProvider;
use Maduser\Argon\Middleware\Provider\RequestHandlerServiceProvider;
use Maduser\Argon\Routing\Provider\RouteServiceProvider;
use Maduser\Argon\View\Provider\ViewServiceProvider;

final class AppServiceProvider extends AbstractServiceProvider
{
    public function register(ArgonContainer $container): void
    {
        $parameters = $container->getParameters();
        $parameters->set('basePath', dirname(__DIR__));

        $container->register(RequestHandlerServiceProvider::class);
        $container->register(MiddlewaresServiceProvider::class);
        $container->register(RouteServiceProvider::class);
        $container->register(ViewServiceProvider::class);
        $container->register(HttpKernelProvider::class);
    }
}
```

`HttpKernelProvider` is application code. Its job is to bind
`Maduser\Argon\Contracts\Handler\HttpKernelInterface` to the HTTP kernel that
should run the request.

## CLI stack

A CLI application follows the same rule: bind a CLI handler and let Prophecy run
it.

```php
<?php

declare(strict_types=1);

use Maduser\Argon\Console\Provider\CliFoundation;
use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;

final class CliAppServiceProvider extends AbstractServiceProvider
{
    public function register(ArgonContainer $container): void
    {
        $container->register(CliFoundation::class);
        $container->register(AppCommandsProvider::class);
    }
}
```

`CliFoundation` is the current Argon console provider. The console package is
separate from Prophecy, so it can evolve without changing the Prophecy runtime
contract.

## Mixed stacks

It is valid to keep one entry point and choose the active handler through your
own service provider logic. Prophecy only needs one resolvable handler when the
lifecycle starts.

```php
public function register(ArgonContainer $container): void
{
    if (PHP_SAPI === 'cli') {
        $container->register(CliAppServiceProvider::class);
        return;
    }

    $container->register(HttpAppServiceProvider::class);
}
```
