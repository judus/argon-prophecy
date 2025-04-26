<?php

declare(strict_types=1);

namespace Maduser\Argon\Support;

use Maduser\Argon\Contracts\Support\HtmlableInterface;
use Stringable;

final class Html implements HtmlableInterface, Stringable
{
    private ?string $rendered = null;

    private function __construct(
        private readonly string $template,
        private readonly array $context = [],
    ) {}

    public static function create(string $template, array $context = []): self
    {
        return new self($template, $context);
    }

    public function toHtml(): string
    {
        if ($this->rendered !== null) {
            return $this->rendered;
        }

        $this->rendered = preg_replace_callback('/{{\s*(\w+)\s*}}/', function ($matches) {
            return array_key_exists($matches[1], $this->context)
                ? (string) $this->context[$matches[1]]
                : $matches[0];
        }, $this->template) ?? '';

        return $this->rendered;
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }
}
