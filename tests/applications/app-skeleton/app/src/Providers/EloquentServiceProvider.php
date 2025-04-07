<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Capsule\Manager;
use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;

class EloquentServiceProvider extends AbstractServiceProvider
{
    public function boot(ArgonContainer $container): void
    {
        $capsule = new Manager();

        $capsule->addConnection([
            'driver'   => 'sqlite',
            'database' => __DIR__ . '/../../resources/database/database.sqlite',
            'prefix'   => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    public function register(ArgonContainer $container): void
    {
        // TODO: Implement register() method.
    }
}
