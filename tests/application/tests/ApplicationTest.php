<?php

declare(strict_types=1);

namespace Tests\Application;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Maduser\Argon\Logging\LoggerServiceProvider;
use Maduser\Argon\Prophecy\Provider\ArgonHttpFoundation;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tests\Application\AbstractHttpTestCase;
use Tests\Application\Mocks\Providers;
use Tests\Application\Mocks\Providers\UserExtendsDefaultStack;

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
        $response = $this->get('/', Providers::DEFAULT_STACK);

        var_dump($response->getBody());

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
