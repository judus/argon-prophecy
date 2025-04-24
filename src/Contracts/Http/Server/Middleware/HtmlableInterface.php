<?php

declare(strict_types=1);

namespace Maduser\Argon\Contracts\Http\Server\Middleware;

interface HtmlableInterface
{
    public function toHtml(): string;
}
