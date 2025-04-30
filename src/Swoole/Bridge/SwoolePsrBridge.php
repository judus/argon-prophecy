<?php

declare(strict_types=1);

namespace Maduser\Argon\Swoole\Bridge;

use Maduser\Argon\Http\Message\Factory\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

final class SwoolePsrBridge
{
    private static ?ServerRequestFactory $factory = null;

    private static function factory(): ServerRequestFactory
    {
        return self::$factory ??= new ServerRequestFactory();
    }

    public static function toPsrRequest(SwooleRequest $swoole): \Psr\Http\Message\ServerRequestInterface
    {
        // Build URI manually from swoole data
        $scheme = (!empty($swoole->header['https']) || $swoole->server['server_port'] === 443) ? 'https' : 'http';
        $host = $swoole->header['host'] ?? 'localhost';
        $path = $swoole->server['request_uri'] ?? '/';
        $query = $swoole->server['query_string'] ?? '';
        $uri = "$scheme://$host$path" . ($query ? "?$query" : '');

        $method = strtoupper($swoole->server['request_method'] ?? 'GET');
        $headers = array_map(fn($v) => [$v], $swoole->header ?? []);
        $server = $swoole->server ?? [];

        $body = new \Maduser\Argon\Http\Message\Stream($swoole->rawContent() ?? '');

        return new \Maduser\Argon\Http\Message\ServerRequest(
            $method,
            new \Maduser\Argon\Http\Message\Uri($uri),
            $headers,
            $body,
            '1.1',
            $server,
            $swoole->cookie ?? [],
            $swoole->get ?? [],
            [], // Uploaded files – you can wire this in later
            $swoole->post ?? null
        );
    }

    public static function emitPsrResponse(ResponseInterface $psr, \Swoole\Http\Response $swoole): void
    {
        // Set status code and reason
        $swoole->status($psr->getStatusCode());

        // Set headers
        foreach ($psr->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                if ($name !== 'content-length') {
                    $swoole->header($name, $value);
                }
            }
        }

        $body = $psr->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        $content = $body->getContents();

        $swoole->header('Content-Length', (string) strlen($content));
        $swoole->end($content);
    }
}
