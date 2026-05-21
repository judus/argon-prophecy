# Registering application integrations

Prophecy does not require a specific ORM, template engine, router, or command
library. Register those integrations through normal Argon service providers.

These examples are application code. They show the pattern, not a requirement.

## Eloquent

If an application wants to use Eloquent, bind and boot it from a provider:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager;
use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;

final class EloquentServiceProvider extends AbstractServiceProvider
{
    public function register(ArgonContainer $container): void
    {
        $container->set(Manager::class);
    }

    public function boot(ArgonContainer $container): void
    {
        $manager = $container->get(Manager::class);

        $manager->addConnection([
            'driver' => 'sqlite',
            'database' => dirname(__DIR__) . '/storage/database.sqlite',
            'prefix' => '',
        ]);

        $manager->setAsGlobal();
        $manager->bootEloquent();
    }
}
```

## Twig through Argon View

When using `maduser/argon-view`, set the application base path and register the
view provider:

```php
<?php

declare(strict_types=1);

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\View\Provider\ViewServiceProvider;

final class ViewAppServiceProvider extends AbstractServiceProvider
{
    public function register(ArgonContainer $container): void
    {
        $container->getParameters()->set('basePath', dirname(__DIR__));
        $container->register(ViewServiceProvider::class);
    }
}
```

The view package registers Twig support for files under
`resources/views`. Prophecy is only involved because the provider is registered
during bootstrap.

## Plain third-party Twig setup

You can also skip `maduser/argon-view` and bind Twig directly:

```php
<?php

declare(strict_types=1);

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class TwigServiceProvider extends AbstractServiceProvider
{
    public function register(ArgonContainer $container): void
    {
        $parameters = $container->getParameters();
        $basePath = (string) $parameters->get('basePath', dirname(__DIR__));
        $viewsPath = rtrim($basePath, '/') . '/resources/views';

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

The same pattern works for other packages: bind the integration in a provider,
boot it if necessary, then let your own handler use it.
