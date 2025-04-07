<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Dotenv\Dotenv;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Prophecy\Argon;
use Maduser\Argon\Prophecy\ServiceProviders\ArgonKernelBindings;

require __DIR__ . '/../../../../../vendor/autoload.php';

Dotenv::createImmutable(__DIR__ . '/../')->load();

Argon::prophecy(function (ArgonContainer $container): void {
    $container->register(ArgonKernelBindings::class);
    $container->register(AppServiceProvider::class);
}, $_ENV['APP_COMPILE_CONTAINER']);
