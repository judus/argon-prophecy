<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Message\Factory;

use Maduser\Argon\Http\Message\ServerRequest;
use Maduser\Argon\Http\Message\Stream;
use Maduser\Argon\Http\Message\UploadedFile;
use Maduser\Argon\Http\Message\Uri;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

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
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return new Uri("$scheme://$host$uri");
    }

    /**
     * @return array<string, string|string[]>
     */
    private static function getAllHeaders(): array
    {
        if (function_exists('getallHeaders')) {
            /** @var array<string, string>|false $headers */
            $headers = getallHeaders();

            if ($headers !== false) {
                $normalized = [];
                foreach ($headers as $key => $value) {
                    $normalized[strtolower($key)] = [$value];
                }
                return $normalized;
            }
        }
        // @codeCoverageIgnoreStart
        return self::parseServerHeaders($_SERVER);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Parses a server array to a header array.
     * Used as a fallback when getallheaders() is unavailable.
     *
     * @param array<array-key, mixed> $server
     * @return array<string, string|string[]>
     */
    public static function parseServerHeaders(array $server): array
    {
        $headers = [];

        /**
         * @var scalar $value
         */
        foreach ($server as $key => $value) {
            if (is_string($key) && is_string($value)) {
                if (str_starts_with($key, 'HTTP_')) {
                    $header = strtolower(str_replace('_', '-', substr($key, 5)));
                    $headers[$header] = [$value];
                } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                    $header = strtolower(str_replace('_', '-', $key));
                    $headers[$header] = [$value];
                }
            }
        }

        return $headers;
    }

    /**
     * Normalizes $_FILES to UploadedFileInterface instances.
     *
     * @param array<array-key, mixed> $files
     * @return array<array-key, UploadedFileInterface|array<array-key, UploadedFileInterface>>
     */
    private static function normalizeUploadedFiles(array $files): array
    {
        /** @var array<string, UploadedFileInterface|array<array-key, UploadedFileInterface>> $normalized */
        $normalized = [];

        foreach ($files as $field => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$field] = $value;
                continue;
            }

            if (!is_array($value) || !isset($value['tmp_name'])) {
                continue;
            }

            if (is_array($value['tmp_name'])) {
                /** @var array<array-key, string> $tmpNames */
                $tmpNames = $value['tmp_name'];

                foreach ($tmpNames as $idx => $tmpName) {
                    if (!isset($normalized[$field]) || !is_array($normalized[$field])) {
                        /** @var array<array-key, UploadedFileInterface> $normalizedField */
                        $normalized[$field] = [];
                    }

                    $normalized[$field][$idx] = self::createUploadedFile([
                        'tmp_name' => $tmpName,
                        'name' => isset($value['name'][$idx]) ? (string) $value['name'][$idx] : null,
                        'type' => isset($value['type'][$idx]) ? (string) $value['type'][$idx] : null,
                        'size' => (int) ($value['size'][$idx] ?? 0),
                        'error' => (int) ($value['error'][$idx] ?? 0),
                    ]);
                }
            } else {
                /** @var array{tmp_name: string, name?: string, type?: string, size?: int, error?: int} $value */
                $normalized[$field] = self::createUploadedFile($value);
            }
        }

        return $normalized;
    }

    private static function createUploadedFile(array $file): UploadedFileInterface
    {
        $tmpName = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';
        $resource = @fopen($tmpName, 'rb');
        if ($resource === false) {
            // @codeCoverageIgnoreStart
            // This can only fail under OS-level conditions (missing file, permissions),
            // which cannot be reliably simulated in unit tests.
            throw new RuntimeException('Failed to open uploaded file: ' . $tmpName);
            // @codeCoverageIgnoreEnd
        }

        $stream = new Stream($resource);

        return new UploadedFile(
            $stream,
            (int) $file['size'],
            (int) $file['error'],
            isset($file['name']) ? (string) $file['name'] : null,
            isset($file['type']) ? (string) $file['type'] : null
        );
    }

    private static function getProtocolVersion(): string
    {
        if (isset($_SERVER['SERVER_PROTOCOL']) && str_starts_with($_SERVER['SERVER_PROTOCOL'], 'HTTP/')) {
            return substr($_SERVER['SERVER_PROTOCOL'], 5);
        }

        return '1.1';
    }
}
