<?php

declare(strict_types=1);

use Tests\Application\Mocks\AppServiceProvider;
use Dotenv\Dotenv;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Contracts\ServiceProviderInterface;
use Maduser\Argon\Logging\LoggerServiceProvider;
use Maduser\Argon\Prophecy\Argon;
use Maduser\Argon\Prophecy\Provider\ArgonHttpFoundation;

require __DIR__ . '/../../../../vendor/autoload.php';

Dotenv::createImmutable(__DIR__ . '/../')->load();

// Set compile flag from header, or fallback to .env
$shouldCompile = (isset($_SERVER['HTTP_X_ARGON_COMPILE']) && $_SERVER['HTTP_X_ARGON_COMPILE'] === 'true')
    ? 'true' : 'false';

Argon::prophecy(function (ArgonContainer $container): void {

    if (!isset($_SERVER['HTTP_X_ARGON_TEST_REQUEST'])) {
        $container->register(LoggerServiceProvider::class);
        $container->register(ArgonHttpFoundation::class);
        return;
    }

    if (isset($_SERVER['HTTP_X_ARGON_TEST_PROVIDER'])) {
        $classes = explode(',', $_SERVER['HTTP_X_ARGON_TEST_PROVIDER']);
        foreach ($classes as $testProviderClass) {
            /** @var class-string<ServiceProviderInterface> $testProviderClass */
            $testProviderClass = trim($testProviderClass);
            if (class_exists($testProviderClass)) {
                $container->register($testProviderClass);
            }
        }
        return;
    }
    throw new Exception('No ServiceProvider provided');
}, $shouldCompile);
