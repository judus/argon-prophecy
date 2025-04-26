<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Support;

interface HtmlableInterface
{
    public function toHtml(): string;
}
