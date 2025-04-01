<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Factory;

use Maduser\Argon\Http\Message\ServerRequest;
use Maduser\Argon\Http\Message\Stream;
use Maduser\Argon\Http\Message\Uri;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ServerRequestFactory implements ServerRequestFactoryInterface
{
    public function __invoke(): ServerRequestInterface
    {
        return self::fromGlobals();
    }

    public static function fromGlobals(): ServerRequestInterface
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = self::createUriFromGlobals();
        $headers = self::getAllHeaders();
        $body = new Stream(fopen('php://input', 'rb'));
        $protocol = self::getProtocolVersion();

        return new ServerRequest(
            $method,
            $uri,
            $headers,
            $body,
            $protocol,
            $_SERVER,
            $_COOKIE,
            $_GET,
            $_POST,
            $_FILES
        );
    }

    private static function createUriFromGlobals(): Uri
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = $scheme . '://' . $host . $requestUri;

        return new Uri($uri);
    }

    private static function getAllHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[ucwords($header, '-')] = $value;
            }

            if (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                $header = str_replace('_', '-', strtolower($key));
                $headers[ucwords($header, '-')] = $value;
            }
        }

        return $headers;
    }

    private static function getProtocolVersion(): string
    {
        if (!empty($_SERVER['SERVER_PROTOCOL']) && str_starts_with($_SERVER['SERVER_PROTOCOL'], 'HTTP/')) {
            return substr($_SERVER['SERVER_PROTOCOL'], 5);
        }

        return '1.1';
    }

    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        // TODO: Implement createServerRequest() method.
    }
}
