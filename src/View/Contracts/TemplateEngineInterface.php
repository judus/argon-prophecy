<?php

declare(strict_types=1);

namespace Maduser\Argon\View\Contracts;

interface TemplateEngineInterface
{
    public function render(string $template, array $context = []): string;
}