<?php

declare(strict_types=1);

namespace App\Exceptions;

use Maduser\Argon\Http\Message\Response;
use Maduser\Argon\Http\Message\Stream;
use Maduser\Argon\Contracts\Http\Exception\ExceptionHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

final class WhoopsExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }
    public function handle(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->error('Unhandled exception', [
            'exception' => $e,
        ]);

        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());
        $whoops->writeToOutput(false);
        $whoops->allowQuit(false);

        $html = $whoops->handleException($e);

        return new Response(
            new Stream($html),
            500,
            ['Content-Type' => ['text/html']]
        );
    }
}
