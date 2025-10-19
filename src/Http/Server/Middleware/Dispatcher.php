<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Middleware;

use Maduser\Argon\Contracts\Http\Server\Middleware\DispatcherInterface;
use Maduser\Argon\Contracts\Http\Server\ResultContextInterface;
use Maduser\Argon\Support\Html;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

final readonly class Dispatcher implements DispatcherInterface
{
    private const TEMPLATE_PATH = __DIR__ . '/../../../../resources/templates/argon-prophecy-welcome.html';

    public function __construct(
        private ResultContextInterface $result,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->logger?->info('DispatcherMiddleware executing dispatch()');

        $this->dispatch($request);

        return $handler->handle($request);
    }

    public function dispatch(ServerRequestInterface $request): void
    {
        $this->logger?->info('Dispatching placeholder logic');

        $html = $this->getPlaceholderHtml();

        $this->result->set(Html::create($html, [
            'argonDispatcher' => '\\' . DispatcherInterface::class,
            'customDispatcher' => '\YourApp\YourDispatcher::class',
        ]));
    }

    private function getPlaceholderHtml(): string
    {
        if (!file_exists(self::TEMPLATE_PATH)) {
            // This throw is only reachable if deployment is broken and the template file is missing
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Missing welcome template at ' . self::TEMPLATE_PATH);
            // @codeCoverageIgnoreEnd
        }

        return file_get_contents(self::TEMPLATE_PATH);
    }
}
