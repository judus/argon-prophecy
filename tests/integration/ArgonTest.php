<?php

declare(strict_types=1);

namespace Tests\Integration\Prophecy;

use Exception;
use Maduser\Argon\Prophecy\Argon;
use PHPUnit\Framework\TestCase;
use Throwable;

class ArgonTest extends TestCase
{
    public function testBootHandlesApplicationCorrectly(): void
    {
        $_ENV['APP_COMPILE_CONTAINER'] = 'false';

        Argon::boot(function () {
            // Empty container configuration for now
        });

        $this->assertTrue(true); // If we got here, no exception, app booted and handled
    }

    public function testProphecyIsAliasForBoot(): void
    {
        $_ENV['APP_COMPILE_CONTAINER'] = 'false';

        Argon::prophecy(function () {
            // Empty container configuration
        }, 'false');

        $this->assertTrue(true); // Again, boot must succeed
    }

    public function testProphecyIsAliasForBootWithCompile(): void
    {
        $_ENV['APP_COMPILE_CONTAINER'] = 'true';
        $_ENV['APP_COMPILE_FILE_NAME'] = '../prophecy.php';
        $_ENV['APP_COMPILE_CLASS_NAME'] = 'Prophecy';
        $_ENV['APP_COMPILE_CLASS_NAMESPACE'] = '';

        Argon::prophecy(function () {
            // Empty container configuration
        }, 'true');

        $this->assertTrue(true); // Again, boot must succeed
    }

    public function testBootHandlesExceptionGracefullyInCli(): void
    {
        $_ENV['APP_COMPILE_CONTAINER'] = 'false';

        try {
            Argon::boot(function () {
                throw new Exception('Boom');
            });
            $this->assertTrue(true, 'Handled exception gracefully');
        } catch (Throwable $e) {
            $this->fail('Exception was not handled: ' . $e->getMessage());
        }
    }

    private function forceCliMode(): void
    {
        if (!defined('PHP_SAPI')) {
            define('PHP_SAPI', 'cli');
        } else {
            // PHP_SAPI is a constant, can't change directly in PHP
            // Assume we're already in CLI for test environment.
        }
    }

    private function restoreSapiMode(string $originalSapi): void
    {
        // Nothing we can do because PHP_SAPI is a compile-time constant
        // But your local env should always be 'cli' during tests anyway
    }
}
