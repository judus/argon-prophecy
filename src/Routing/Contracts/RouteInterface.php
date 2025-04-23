<?php

namespace Maduser\Argon\Routing\Contracts;

use Closure;

interface RouteInterface
{
    public function getName(): ?string;

    public function getPattern(): string;

    public function getMethod(): string;

    public function getHandler(): string|array|Closure;

    public function setPipelineId(?string $pipelineId): void;

    public function getPipelineId(): ?string;

    /**
     * @param list<class-string> $middlewares
     * @return void
     */
    public function setMiddlewares(array $middlewares): void;

    public function getMiddlewares(): array;

    /**
     * @param array<int|string, string> $args
     * @return void
     */
    public function setArguments(array $args): void;

    public function getArguments(): array;

    public function toArray(): array;
}