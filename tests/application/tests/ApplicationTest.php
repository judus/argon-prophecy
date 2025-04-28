<?php

declare(strict_types=1);

namespace Tests\Application;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Tests\Application\Mocks\Providers;

class ApplicationTest extends AbstractHttpTestCase
{
    private function assertIsDefaultHtmlContent(ResponseInterface $response): void
    {
        $body = (string) $response->getBody();

        $this->assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('<title>Argon Prophecy – Getting Started</title>', $body);
        $this->assertStringContainsString('DispatcherMiddleware', $body);
        $this->assertStringContainsString('Argon has booted successfully', $body);
    }

    /**
     * @throws GuzzleException
     */
    public function testDefaultStackReturns200WithExpectedContents(): void
    {
        $response = $this->get('/', Providers::NO_LOGGER_STACK);

        $this->assertOk($response);
        $this->assertIsDefaultHtmlContent($response);
    }

    /**
     * @throws GuzzleException
     */
    public function testNoLoggerStackReturns200WithExpectedContents(): void
    {
        $response = $this->get('/', Providers::NO_LOGGER_STACK);

        $this->assertOk($response);
        $this->assertIsDefaultHtmlContent($response);
    }

    /**
     * @throws GuzzleException
     */
    public function testUserExtendsStackReturns200WithExpectedContents(): void
    {
        $response = $this->get('/', Providers::USER_EXTENDS_DEFAULT_STACK);

        $this->assertOk($response);
        $this->assertIsDefaultHtmlContent($response);
    }
}
