<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Logging\LoggerServiceProvider;
use Maduser\Argon\Prophecy\Argon;
use Maduser\Argon\Prophecy\Provider\ArgonHttpFoundation;
use Maduser\Argon\Swoole\Bridge\SwoolePsrBridge;
use Psr\Log\LoggerInterface;
use Swoole\Http\Server;

$logger = null;

Dotenv::createImmutable(__DIR__)->load();

Argon::boot(function (ArgonContainer $container) use (&$logger): void {
    $container->register(ArgonHttpFoundation::class);
}, $_ENV['APP_COMPILE_CONTAINER']);

$server = new Server("127.0.0.1", 9501);

$server->on("request", function ($request, $response) use ($logger) {
    try {
        $psrRequest = SwoolePsrBridge::toPsrRequest($request);
        $psrResponse = Argon::process($psrRequest);
        SwoolePsrBridge::emitPsrResponse($psrResponse, $response);
    } catch (Throwable $throwable) {
        $response->status(500);
        $response->end("Internal Server Error: " . $throwable->getMessage());
    }
});

$server->start();
