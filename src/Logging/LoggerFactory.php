<?php

declare(strict_types=1);

namespace Maduser\Argon\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final readonly class LoggerFactory
{
    public function __construct(
        private int $logLevel = 100,
        private ?string $logFile = null
    ) {
    }

    public function create(): LoggerInterface
    {
        if (!class_exists('Monolog\Logger') || !class_exists('Monolog\Handler\StreamHandler')) {
            return new NullLogger();
        }

        $logFile = $this->logFile ?? match (PHP_SAPI) {
            'cli', 'cli-server' => 'php://stdout',
            default => 'php://stderr',
        };

        $logger = new Logger('argon');

        $logger->pushHandler(new StreamHandler($logFile, $this->logLevel));

        assert($logger instanceof LoggerInterface);

        return $logger;
    }
}
