<?php

namespace App\Exception;

use Maduser\Argon\Http\Message\Response;
use Maduser\Argon\Http\Message\Stream;
use Maduser\Argon\Kernel\Exception\ExceptionHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

final class WhoopsExceptionHandler implements ExceptionHandlerInterface
{
    public function report(Throwable $throwable): void
    {
        error_log((string) $throwable);
    }

    public function render(Throwable $throwable): ResponseInterface
    {
        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());
        $whoops->writeToOutput(false);
        $whoops->allowQuit(false);

        $html = $whoops->handleException($throwable);

        return new Response(
            new Stream($html),
            500,
            ['Content-Type' => ['text/html']]
        );
    }
}