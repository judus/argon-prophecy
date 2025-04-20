<?php

declare(strict_types=1);

namespace Maduser\Argon\View\Contracts;

interface HtmlableInterface
{
    public function toHtml(): string;
}