<?php

declare(strict_types=1);

namespace Maduser\Argon\Routing;

use Maduser\Argon\Middleware\Contracts\PipelineStoreInterface;
use Maduser\Argon\Middleware\MiddlewareStack;
use Maduser\Argon\Routing\Contracts\RequestHandlerResolverInterface;
use Maduser\Argon\Routing\Contracts\RouteContextInterface;
use Maduser\Argon\Routing\Contracts\RouteMatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class RequestHandlerResolver implements RequestHandlerResolverInterface
{
    public function __construct(
        private RouteMatcherInterface $matcher,
        private RouteContextInterface $context,
        private PipelineStoreInterface $pipelines,
        private LoggerInterface $logger,
    ) {
    }

    public function resolve(ServerRequestInterface $request): ResolvedRequestHandler
    {
        $route = $this->matcher->match($request);
        $request = $this->context->set($request, $route);

        $this->logger->info('Matched route', [
            'name' => $route->getName(),
            'arguments' => $route->getArguments(),
            'pipelineId' => $route->getPipelineId(),
        ]);

        $pipeline = $route->getPipelineId() !== null
            ? $this->pipelines->get($route->getPipelineId())
            : $this->pipelines->get(new MiddlewareStack($route->getMiddlewares()));

        $this->logger->info('RequestHandler resolved', [
            'pipeline' => $route->getPipelineId() ?? (new MiddlewareStack($route->getMiddlewares()))->toArray(),
        ]);

        return new ResolvedRequestHandler($pipeline, $request);
    }
}
