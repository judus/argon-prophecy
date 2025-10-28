<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\ErrorHandling;

enum BootstrapErrorHandlerMode: string
{
    case CLI = 'cli';
    case CLI_SERVER = 'cli-server';
    case HTTP = 'http';
}
