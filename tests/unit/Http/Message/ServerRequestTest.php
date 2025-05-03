<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Message;

use InvalidArgumentException;
use Maduser\Argon\Http\Message\ServerRequest;
use Maduser\Argon\Http\Message\Stream;
use Maduser\Argon\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

final class ServerRequestTest extends TestCase
{
    public function testProtocolVersion(): void
    {
        $request = new ServerRequest();
        $this->assertSame('1.1', $request->getProtocolVersion());

        $new = $request->withProtocolVersion('2.0');
        $this->assertSame('2.0', $new->getProtocolVersion());
    }

    public function testHeaders(): void
    {
        $request = new ServerRequest(headers: ['Content-Type' => ['text/plain']]);
        $this->assertTrue($request->hasHeader('Content-Type'));
        $this->assertSame(['text/plain'], $request->getHeader('Content-Type'));
        $this->assertSame('text/plain', $request->getHeaderLine('Content-Type'));

        $new = $request->withHeader('X-Test', 'Value');
        $this->assertSame(['Value'], $new->getHeader('X-Test'));

        $new = $new->withAddedHeader('X-Test', 'Another');
        $this->assertSame(['Value', 'Another'], $new->getHeader('X-Test'));

        $new = $new->withoutHeader('X-Test');
        $this->assertFalse($new->hasHeader('X-Test'));
    }

    public function testBody(): void
    {
        $request = new ServerRequest();
        $stream = new Stream('foobar');
        $new = $request->withBody($stream);
        $this->assertSame($stream, $new->getBody());
    }

    public function testRequestTarget(): void
    {
        $request = new ServerRequest(uri: new Uri('/foo?bar=baz'));
        $this->assertSame('/foo?bar=baz', $request->getRequestTarget());

        $new = $request->withRequestTarget('/custom');
        $this->assertSame('/custom', $new->getRequestTarget());
    }

    public function testMethod(): void
    {
        $request = new ServerRequest(method: 'post');
        $this->assertSame('POST', $request->getMethod());

        $new = $request->withMethod('put');
        $this->assertSame('PUT', $new->getMethod());
    }

    public function testUri(): void
    {
        $request = new ServerRequest();
        $uri = new Uri('https://example.com');

        $new = $request->withUri($uri, preserveHost: false);
        $this->assertSame($uri, $new->getUri());
        $this->assertSame(['example.com'], $new->getHeader('host'));

        $preserved = $request->withUri($uri, preserveHost: true);
        $this->assertSame($uri, $preserved->getUri());
        $this->assertFalse($preserved->hasHeader('host'));
    }

    public function testServerParams(): void
    {
        $params = ['SERVER_NAME' => 'localhost'];
        $request = new ServerRequest(serverParams: $params);
        $this->assertSame($params, $request->getServerParams());
    }

    public function testCookieParams(): void
    {
        $cookies = ['foo' => 'bar'];
        $request = new ServerRequest(cookieParams: $cookies);
        $this->assertSame($cookies, $request->getCookieParams());

        $new = $request->withCookieParams(['baz' => 'qux']);
        $this->assertSame(['baz' => 'qux'], $new->getCookieParams());
    }

    public function testQueryParams(): void
    {
        $query = ['foo' => 'bar'];
        $request = new ServerRequest(queryParams: $query);
        $this->assertSame($query, $request->getQueryParams());

        $new = $request->withQueryParams(['baz' => 'qux']);
        $this->assertSame(['baz' => 'qux'], $new->getQueryParams());
    }

    public function testUploadedFiles(): void
    {
        $uploads = ['file1' => 'something'];
        $request = new ServerRequest(uploadedFiles: $uploads);
        $this->assertSame($uploads, $request->getUploadedFiles());

        $new = $request->withUploadedFiles(['file2' => 'another']);
        $this->assertSame(['file2' => 'another'], $new->getUploadedFiles());
    }

    public function testParsedBody(): void
    {
        $body = ['foo' => 'bar'];
        $request = new ServerRequest(parsedBody: $body);
        $this->assertSame($body, $request->getParsedBody());

        $new = $request->withParsedBody(['baz' => 'qux']);
        $this->assertSame(['baz' => 'qux'], $new->getParsedBody());
    }

    public function testAttributes(): void
    {
        $request = new ServerRequest(attributes: ['foo' => 'bar']);
        $this->assertSame('bar', $request->getAttribute('foo'));
        $this->assertSame('default', $request->getAttribute('missing', 'default'));

        $new = $request->withAttribute('baz', 'qux');
        $this->assertSame('qux', $new->getAttribute('baz'));

        $removed = $new->withoutAttribute('baz');
        $this->assertNull($removed->getAttribute('baz'));
    }

    public function testGetHeaders(): void
    {
        $request = new ServerRequest(headers: ['X-Test' => ['Value']]);
        $this->assertSame(['x-test' => ['Value']], $request->getHeaders());
    }

    public function testGetAttributes(): void
    {
        $request = new ServerRequest(attributes: ['key' => 'value']);
        $this->assertSame(['key' => 'value'], $request->getAttributes());
    }
}
