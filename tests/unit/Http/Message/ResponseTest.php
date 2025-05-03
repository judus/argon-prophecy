<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Message;

use Maduser\Argon\Http\Message\Response;
use Maduser\Argon\Http\Message\Stream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Maduser\Argon\Http\Message\Response
 */
final class ResponseTest extends TestCase
{
    public function testDefaultConstructorValues(): void
    {
        $response = new Response();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertTrue($response->hasHeader('content-length'));
    }

    public function testWithProtocolVersion(): void
    {
        $response = (new Response())->withProtocolVersion('2.0');
        $this->assertSame('2.0', $response->getProtocolVersion());
    }

    public function testHeaderManipulation(): void
    {
        $response = (new Response())
            ->withHeader('X-Test', 'value')
            ->withAddedHeader('X-Test', 'another')
            ->withoutHeader('content-length');

        $this->assertSame(['value', 'another'], $response->getHeader('x-test'));
        $this->assertFalse($response->hasHeader('content-length'));
    }

    public function testGetHeadersReturnsExpectedArray(): void
    {
        $response = new Response();
        $responseWithHeader = $response->withHeader('X-Test', 'value');

        $headers = $responseWithHeader->getHeaders();

        $this->assertArrayHasKey('x-test', $headers);
        $this->assertEquals(['value'], $headers['x-test']);
    }

    public function testBodyManipulation(): void
    {
        $stream = Stream::fromString('foobar');
        $response = (new Response())->withBody($stream);

        $this->assertSame('foobar', (string) $response->getBody());

        $appended = $response->appendBody('baz');
        $this->assertStringContainsString('baz', (string) $appended->getBody());
    }

    public function testWithStatusAndReason(): void
    {
        $response = (new Response())->withStatus(404);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getReasonPhrase());
    }

    public function testWithCustomStatusMessage(): void
    {
        $response = (new Response())->withStatusMessage('Custom Reason');

        $this->assertSame('Custom Reason', $response->getReasonPhrase());
    }

    public function testWithJson(): void
    {
        $data = ['key' => 'value'];
        $response = (new Response())->withJson($data);

        $this->assertStringContainsString('application/json', $response->getHeaderLine('content-type'));
        $this->assertJson((string) $response->getBody());
    }

    public function testWithHtml(): void
    {
        $html = '<p>Hello</p>';
        $response = (new Response())->withHtml($html);

        $this->assertStringContainsString('text/html', $response->getHeaderLine('content-type'));
        $this->assertStringContainsString($html, (string) $response->getBody());
    }

    public function testWithText(): void
    {
        $text = 'plain text';
        $response = (new Response())->withText($text);

        $this->assertStringContainsString('text/plain', $response->getHeaderLine('content-type'));
        $this->assertStringContainsString($text, (string) $response->getBody());
    }

    public function testStaticFactoryMethods(): void
    {
        $text = Response::text('hello');
        $this->assertStringContainsString('text/plain', $text->getHeaderLine('content-type'));

        $html = Response::html('<b>html</b>');
        $this->assertStringContainsString('text/html', $html->getHeaderLine('content-type'));

        $json = Response::json(['foo' => 'bar']);
        $this->assertStringContainsString('application/json', $json->getHeaderLine('content-type'));
    }
}
