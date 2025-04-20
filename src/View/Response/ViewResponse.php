<?php

declare(strict_types=1);

namespace Maduser\Argon\View\Response;

use Maduser\Argon\View\Contracts\HtmlableInterface;
use Maduser\Argon\View\Contracts\TemplateEngineInterface;

final readonly class ViewResponse implements HtmlableInterface
{
    public function __construct(
        private TemplateEngineInterface $engine,
        private string $template,
        private array $data = []
    ) {}

    public function toHtml(): string
    {
        return $this->engine->render($this->template, $this->data);
    }
}
