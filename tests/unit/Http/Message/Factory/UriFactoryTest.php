<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Message\Factory;

use Maduser\Argon\Http\Message\Factory\UriFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class UriFactoryTest extends TestCase
{
    public function testCreateUri(): void
    {
        $factory = new UriFactory();
        $uri = $factory->createUri('https://example.com/test?query=1#frag');

        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame('/test', $uri->getPath());
        $this->assertSame('query=1', $uri->getQuery());
        $this->assertSame('frag', $uri->getFragment());
    }
}
