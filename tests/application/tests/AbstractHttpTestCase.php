<?php

declare(strict_types=1);

namespace Tests\Application;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

abstract class AbstractHttpTestCase extends TestCase
{
    protected static int $pid = 0;
    protected static Client $client;
    protected static string $serverHost = '127.0.0.1:8080';
    protected static string $serverBaseUri = 'http://127.0.0.1:8080';
    protected static string $docRoot = __DIR__ . '/../app/public';

    public static function setUpBeforeClass(): void
    {
        $alreadyRunning = self::serverIsReachable();

        if ($alreadyRunning) {
            echo "⚠ Dev server already running at " . self::$serverBaseUri . " — skipping launch.\n";
            self::$pid = 0;
        } else {
            $docRoot = realpath(self::$docRoot);
            $cmd = "php -S " . self::$serverHost . " -t {$docRoot} > /dev/null 2>&1 & echo $!";

            /**
             * @psalm-suppress ForbiddenCode
             */
            $output = shell_exec($cmd);

            if ($output === null) {
                throw new RuntimeException('Failed to start built-in PHP server.');
            }

            self::$pid = (int) $output;
            echo "🚀 Dev server started at " . self::$serverBaseUri . " (PID: " . self::$pid . ")\n";
        }

        self::waitUntilServerReady();

        self::$client = new Client([
            'base_uri' => self::$serverBaseUri,
            'http_errors' => false,
        ]);
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$pid > 0) {
            posix_kill(self::$pid, SIGTERM);
            echo "🧼 Dev server (PID: " . self::$pid . ") terminated.\n";
        } else {
            echo "ℹ No server to shut down.\n";
        }
    }

    /**
     * @param list<class-string>|null $serviceProviders
     * @throws GuzzleException
     */
    protected function get(string $uri = '/', ?array $serviceProviders = null, bool $compile = false): ResponseInterface
    {
        $headers = [];

        $headers['X-Argon-Test-Request'] = '1';

        if ($serviceProviders !== null) {
            $headers['X-Argon-Test-Provider'] = implode(',', $serviceProviders);
        }

        $headers['X-Argon-Compile'] = $compile ? 'true' : 'false';

        return self::$client->get($uri, [
            'headers' => $headers,
        ]);
    }

    protected function assertOk(ResponseInterface $response): void
    {
        $status = $response->getStatusCode();

        if ($status >= 400) {
            $body = (string) $response->getBody();
            echo "\n\n🔥 HTTP {$status} Error Response:\n";
            echo $body . "\n\n";
        }

        $this->assertSame(200, $status, "Expected 200 OK, got {$status}");
    }

    private static function serverIsReachable(): bool
    {
        $context = stream_context_create(['http' => ['timeout' => 1]]);

        return @file_get_contents(self::$serverBaseUri, false, $context) !== false;
    }

    private static function waitUntilServerReady(): void
    {
        $timeout = microtime(true) + 5;

        while (microtime(true) < $timeout) {
            if (self::serverIsReachable()) {
                return;
            }

            usleep(200000);
        }

        throw new RuntimeException('Timed out waiting for dev server at ' . self::$serverBaseUri);
    }
}
