<?php

declare(strict_types=1);

namespace Tests\Applications\AppSkeleton;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

class AppSkeletonTest extends AbstractHttpTestCase
{
    /**
     * @throws GuzzleException
     */
    public function testIndexReturnsJsonSerializable(): void
    {
        $response = self::$client->get('/');

        $this->assertSame(200, $response->getStatusCode());

        $this->assertStringContainsString(
            'application/json',
            $response->getHeaderLine('Content-Type')
        );

        $data = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($data);
        $this->assertSame('Argon Prophecy', $data['name']);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+/', $data['version']);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $data['current_time']);
    }

    public function testRouteParamsAreInjected(): void
    {
        $response = self::$client->get('/demo/params/foo/bar');
        $data = json_decode((string) $response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('foo', $data['id']);
        $this->assertSame('bar', $data['category']);
    }

    public function testDependencyIsInjected(): void
    {
        $response = self::$client->get('/demo/injected');
        $data = json_decode((string) $response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('result', $data);
        $this->assertIsString($data['result']);
        $this->assertStringContainsString('test string', $data['result']);
    }

    /**
     * @throws GuzzleException
     */
    public function testDependencyAndParam(): void
    {
        $response = self::$client->get('/demo/injected/42');
        $data = json_decode((string) $response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('42', $data['id']);
        $this->assertStringContainsString('test string', $data['from_test_class']);
    }

    /**
     * @throws GuzzleException
     */
    public function testPlainStringResponse(): void
    {
        $response = self::$client->get('/demo/plain');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('text/plain', $response->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('Just a plain string', (string) $response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testExceptionHandlingReturns500(): void
    {
        $response = self::$client->get('/demo/error');

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('This is a test exception', (string) $response->getBody());
    }
}
