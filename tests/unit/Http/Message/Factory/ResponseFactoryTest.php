<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Message\Factory;

use JsonException;
use Maduser\Argon\Http\Message\Factory\ResponseFactory;
use Maduser\Argon\Http\Message\Factory\StreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ResponseFactoryTest extends TestCase
{
    private ResponseFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new ResponseFactory(new StreamFactory());
    }

    public function testCreateResponseDefaults(): void
    {
        $response = $this->factory->createResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', (string) $response->getBody());
    }

    public function testCreateResponseWithCustomStatusAndReason(): void
    {
        $response = $this->factory->createResponse(404, 'Not Found');

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getReasonPhrase());
    }

    public function testTextResponse(): void
    {
        $response = $this->factory->text('Hello World', 201);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('Hello World', (string) $response->getBody());
        $this->assertSame('text/plain', $response->getHeaderLine('Content-Type'));
    }

    public function testHtmlResponse(): void
    {
        $response = $this->factory->html('<h1>Title</h1>', 202);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('<h1>Title</h1>', (string) $response->getBody());
        $this->assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    /**
     * @throws JsonException
     */
    public function testJsonResponse(): void
    {
        $response = $this->factory->json(['key' => 'value']);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"key":"value"}', (string) $response->getBody());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testJsonResponseThrowsOnInvalidData(): void
    {
        $this->expectException(JsonException::class);

        // JSON cannot encode INF or NAN
        $this->factory->json(['bad' => INF]);
    }
}
