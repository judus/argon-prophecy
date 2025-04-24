<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Middleware;

use Maduser\Argon\Contracts\Http\Server\Middleware\DispatcherInterface;
use Maduser\Argon\Contracts\Support\ResultContextInterface;
use Maduser\Argon\Support\Html;
use Maduser\Argon\Support\ResultContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class Dispatcher implements MiddlewareInterface, DispatcherInterface
{
    public function __construct(
        private ResultContextInterface $result,
        private LoggerInterface $logger,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->logger->info('DispatcherMiddleware executing dispatch()');

        $this->dispatch($request);

        return $handler->handle($request);
    }

    public function dispatch(ServerRequestInterface $request): void
    {
        $this->logger->info('Dispatching placeholder logic');

        // Argon Prophecy is done here, rest is up to you.
        // You would probably want to execute some logic or controller here.
        $this->result->set(Html::create(<<<'HTML'
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Argon Prophecy – Getting Started</title>
                <style>
                    html, body {
                        margin: 0;
                        padding: 0;
                        height: 100%;
                        font-family: system-ui, sans-serif;
                        background: linear-gradient(to bottom right, #f9f9f9, #f0f0f0);
                        color: #222;
                        display: flex;
                        flex-direction: column;
                    }
                
                    main {
                        background: #fff;
                        border-left: 6px solid #4A90E2;
                        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
                        padding: 2rem 3rem;
                        border-radius: 8px;
                        max-width: 720px;
                        margin: auto;
                    }
                
                    h1 {
                        margin-bottom: 1rem;
                        color: #222;
                        font-size: 2rem;
                    }
                
                    h2 {
                        margin-top: 2rem;
                        color: #333;
                        font-size: 1.2rem;
                    }
                
                    p {
                        max-width: 640px;
                        margin: 0.5rem auto;
                        line-height: 1.6;
                    }
                
                    pre {
                        background: #f5f5f5;
                        padding: 1rem;
                        border-left: 4px solid #4A90E2;
                        border-radius: 6px;
                        overflow-x: auto;
                    }
                
                    code {
                        font-family: monospace;
                        background-color: #f0f0f0;
                        padding: 0.1rem 0.3rem;
                        border-radius: 3px;
                    }
                </style>
            </head>
            <body>
                <main>
                    <h1>Welcome to Argon Prophecy</h1>
                    <p>This is the default response from <code>DispatcherMiddleware</code>.</p>
                    <p>No dispatcher has been configured to handle this request.</p>
                
                    <h2>What's going on?</h2>
                    <p>Argon has booted successfully and the middleware stack is running.</p>
                    <p>But the final step – dispatching a request to your application logic – has not been customized yet.</p>
                
                    <h2>Next steps</h2>
                    <p>To define how your app handles requests, bind your own dispatcher to:</p>
                    <pre><code>{{ argonDispatcher }}</code></pre>
                
                    <p>This is where your controllers, routes, closures, or handlers should be invoked.</p>
                
                    <p>Example binding in your <code>AppServiceProvider</code>:</p>
                <pre><code>$container->set(
                {{ argonDispatcher }},
                {{ customDispatcher }},
            );</code></pre>
                
                    <h2>But wait — there's more</h2>
                    <p>This isn't just about dispatching. Every component in Argon is replaceable.</p>
                    <p>Check out <code>ArgonHttpFoundation</code> and <code>ArgonConsoleFoundation</code> to see what's registered and how you can override it.</p>
                
                    <p>Need a custom kernel, router, logger, view engine, or error handler? Just rebind the interface.</p>
                
                    <p>Build only what you need, override what you want. Your app, your architecture.</p>
                </main>
            </body>
            </html>
            HTML, [
            'argonDispatcher' => '\\' . DispatcherInterface::class,
            'customDispatcher' => '\YourApp\YourDispatcher::class',
        ]));
    }
}
