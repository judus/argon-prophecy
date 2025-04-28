<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Message;

use InvalidArgumentException;
use Maduser\Argon\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    public function testValidUriParsing(): void
    {
        $uri = new Uri('https://user:pass@example.com:8080/path?query=1#fragment');

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path', $uri->getPath());
        $this->assertSame('query=1', $uri->getQuery());
        $this->assertSame('fragment', $uri->getFragment());
        $this->assertSame('https://user:pass@example.com:8080/path?query=1#fragment', (string) $uri);
    }

    public function testMalformedUriThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Uri('thiswillnotparse');
    }

    public function testInvalidSchemeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Uri('1nvalid://example.com');
    }

    public function testInvalidPortThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new Uri())->withPort(70000);
    }

    public function testGetAuthorityReturnsEmptyWhenNoHost(): void
    {
        $uri = new Uri('/some/path');
        $this->assertSame('', $uri->getAuthority());
    }

    public function testParseUrlFailsWithInvalidUri(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to parse URI');

        new Uri('http://localhost:70000');
    }

    public function testWithMethods(): void
    {
        $uri = new Uri();
        $uri = $uri
            ->withScheme('https')
            ->withUserInfo('user', 'pass')
            ->withHost('example.com')
            ->withPort(443)
            ->withPath('/test')
            ->withQuery('foo=bar')
            ->withFragment('frag');

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(443, $uri->getPort());
        $this->assertSame('/test', $uri->getPath());
        $this->assertSame('foo=bar', $uri->getQuery());
        $this->assertSame('frag', $uri->getFragment());
        $this->assertSame('https://user:pass@example.com:443/test?foo=bar#frag', (string) $uri);
    }
}
