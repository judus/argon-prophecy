<?php

declare(strict_types=1);

namespace Tests\Application;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractHttpTestCase extends TestCase
{
    protected static int $pid = 0;
    protected static Client $client;
    protected static string $server = '0.0.0.0:8080';
    protected static string $docRoot = __DIR__ . '/../app/public';

    public static function setUpBeforeClass(): void
    {
        $context = stream_context_create(['http' => ['timeout' => 1]]);
        $alreadyRunning = @file_get_contents("http://" . self::$server, false, $context) !== false;

        if ($alreadyRunning) {
            echo "⚠ Dev server already running at http://" . self::$server . " — skipping launch.\n";
            self::$pid = 0;
        } else {
            $docRoot = realpath(self::$docRoot);
            $cmd = "php -S " . self::$server . " -t {$docRoot} > /dev/null 2>&1 & echo $!";

            /**
             * @psalm-suppress ForbiddenCode
             */
            $output = shell_exec($cmd);

            if ($output === null) {
                throw new \RuntimeException('Failed to start built-in PHP server.');
            }

            self::$pid = (int) $output;
            echo "🚀 Dev server started at http://" . self::$server . " (PID: " . self::$pid . ")\n";
            sleep(1);
        }

        self::$client = new Client([
            'base_uri' => self::$server,
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
}
