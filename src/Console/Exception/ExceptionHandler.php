<?php

declare(strict_types=1);

namespace Maduser\Argon\Console\Exception;

use Maduser\Argon\Contracts\Console\Exception\ExceptionFormatterInterface;
use Maduser\Argon\Contracts\Console\Exception\ExceptionHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class ExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(
        private ExceptionFormatterInterface $formatter,
        private ?LoggerInterface            $logger = null,
    ) {
    }

    public function handle(Throwable $e): int
    {
        $this->logger?->error('Unhandled CLI exception', ['exception' => $e]);

        try {
            return $this->formatter->format($e);
        } catch (Throwable $fail) {
            fwrite(STDERR, "Critical failure: {$fail->getMessage()}\\n\\");
            return 1;
        }
    }
}
