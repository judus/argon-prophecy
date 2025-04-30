<?php

declare(strict_types=1);

// Only load if needed
if (! function_exists('opcache_compile_file')) {
    throw new RuntimeException('OPcache extension is not available.');
}

// Clean helper
function preloadFile(string $file): void
{
    if (! is_file($file)) {
        throw new RuntimeException(sprintf('Cannot preload missing file: %s', $file));
    }

    opcache_compile_file($file);
}

// The critical core files
$files = [
    __DIR__.'/vendor/composer/autoload_real.php',
    __DIR__.'/vendor/autoload.php',
    __DIR__.'/src/Http/Message/ServerRequest.php',
    __DIR__.'/src/Http/Message/Response.php',
    __DIR__.'/src/Http/Server/Middleware/HtmlResponder.php',
    __DIR__.'/src/Http/Server/Middleware/Dispatcher.php',
    __DIR__.'/src/Http/Server/MiddlewarePipeline.php',
    __DIR__.'/src/Http/Kernel.php',
    __DIR__.'/src/Prophecy/Application.php',
    __DIR__.'/src/Prophecy/Argon.php',
    __DIR__.'/prophecy.php',
];

// Preload each file
foreach ($files as $file) {
    preloadFile($file);
}
