<?php

declare(strict_types=1);

namespace Maduser\Argon\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final readonly class LoggerFactory
{
    /**
     * @param int $logLevel
     * @param string|null $logFile
     * @param string $loggerClass
     * @param string $handlerClass
     */
    public function __construct(
        private int $logLevel = 100,
        private ?string $logFile = null,
        private string $loggerClass = "\Monolog\Logger",
        private string $handlerClass = "\Monolog\Handler\StreamHandler"
    ) {
    }

    public function create(): LoggerInterface
    {
        if (!class_exists($this->loggerClass) || !class_exists($this->handlerClass)) {
            return new NullLogger();
        }

        $logFile = $this->logFile ?? match (PHP_SAPI) {
            'cli', 'cli-server' => 'php://stdout',
            default => 'php://stderr',
        };

        /** @var class-string<Logger> $loggerClass */
        $loggerClass = $this->loggerClass;

        /** @var class-string<StreamHandler> $handlerClass */
        $handlerClass = $this->handlerClass;

        $logger = new ($loggerClass)('argon');
        $handler = new ($handlerClass)($logFile, $this->logLevel);

        $logger->pushHandler($handler);

        assert($logger instanceof LoggerInterface);

        return $logger;
    }
}
