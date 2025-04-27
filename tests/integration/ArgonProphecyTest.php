<?php

declare(strict_types=1);

namespace Tests\Integration;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Prophecy\Argon;
use PHPUnit\Framework\TestCase;
use Tests\Application\Mocks\Providers;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Maduser\Argon\Contracts\KernelInterface;
use Maduser\Argon\Http\Message\Uri;

final class ArgonProphecyTest extends TestCase
{
    public function setUp(): void
    {
        Argon::reset();
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
        $this->assertSame('OK', $response->getReasonPhrase());
    }

    private function assertIsDefaultHtmlContent(ResponseInterface $response): void
    {
        $body = (string) $response->getBody();

        $this->assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('<title>Argon Prophecy – Getting Started</title>', $body);
        $this->assertStringContainsString('DispatcherMiddleware', $body);
        $this->assertStringContainsString('Argon has booted successfully', $body);
    }

    public function testCaptureFlow(): void
    {
        Argon::boot(function (ArgonContainer $container): void {
            $container->register(Providers::DEFAULT_STACK);
        });

        $response = Argon::process();

        $this->assertOk($response);
        $this->assertIsDefaultHtmlContent($response);
    }

    public function testEmitFlow(): void
    {
        Argon::boot(function (ArgonContainer $container): void {
            $container->register(Providers::DEFAULT_STACK);
        });

        $response = Argon::process();

        Argon::emit($response);

        $this->assertOk($response);
    }
}
