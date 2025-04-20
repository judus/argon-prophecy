<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Message\Factory;

use Maduser\Argon\Http\Message\ServerRequest;
use Maduser\Argon\Http\Message\Stream;
use Maduser\Argon\Http\Message\UploadedFile;
use Maduser\Argon\Http\Message\Uri;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\UploadedFileInterface;

final class ServerRequestFactory implements ServerRequestFactoryInterface
{
    public function __invoke(): ServerRequestInterface
    {
        return self::fromGlobals();
    }

    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        $uri = $uri instanceof UriInterface ? $uri : new Uri($uri);
        $body = new Stream(fopen('php://temp', 'r+'));

        return new ServerRequest($method, $uri, [], $body, '1.1', $serverParams);
    }

    public static function fromGlobals(): ServerRequestInterface
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = self::createUriFromGlobals();
        $headers = self::getAllHeaders();
        $body = new Stream(fopen('php://input', 'rb'));
        $protocol = self::getProtocolVersion();
        $files = self::normalizeUploadedFiles($_FILES);
        $parsedBody = $method === 'POST' ? $_POST : null;

        return new ServerRequest(
            $method,
            $uri,
            $headers,
            $body,
            $protocol,
            $_SERVER,
            $_COOKIE,
            $_GET,
            $files,
            $parsedBody
        );
    }

    private static function createUriFromGlobals(): UriInterface
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return new Uri("$scheme://$host$uri");
    }

    private static function getAllHeaders(): array
    {
        $headers = [];

        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $key => $value) {
                $headers[strtolower($key)] = [$value];
            }
            return $headers;
        }

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$header] = [$value];
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                $header = strtolower(str_replace('_', '-', $key));
                $headers[$header] = [$value];
            }
        }

        return $headers;
    }

    /**
     * Normalizes $_FILES to UploadedFileInterface instances
     */
    private static function normalizeUploadedFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $field => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$field] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$field] = self::createUploadedFile($value);
            } elseif (is_array($value)) {
                $normalized[$field] = self::normalizeUploadedFiles($value);
            }
        }

        return $normalized;
    }

    private static function createUploadedFile(array $file): UploadedFileInterface
    {
        $stream = new Stream(fopen($file['tmp_name'], 'rb'));
        return new UploadedFile(
            $stream,
            (int)$file['size'],
            (int)$file['error'],
            $file['name'] ?? null,
            $file['type'] ?? null
        );
    }

    private static function getProtocolVersion(): string
    {
        if (!empty($_SERVER['SERVER_PROTOCOL']) && str_starts_with($_SERVER['SERVER_PROTOCOL'], 'HTTP/')) {
            return substr($_SERVER['SERVER_PROTOCOL'], 5);
        }

        return '1.1';
    }
}
