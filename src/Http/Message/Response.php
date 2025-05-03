<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Message;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class Response implements ResponseInterface
{
    /** @var array<lowercase-string, list<string>> */
    private array $headers;
    private string $protocol;
    private int $status;
    private string $reasonPhrase;
    private StreamInterface $body;

    public function __construct(
        StreamInterface $body = null,
        int $status = 200,
        array $headers = [],
        string $protocol = '1.1',
        string $reasonPhrase = 'OK'
    ) {
        $this->status = $status;
        $this->body = $body ?? new Stream(fopen('php://temp', 'r+'));
        /** @var array<string, string|string[]> $headers */
        $this->headers = $this->normalizeHeaders($headers);
        $this->protocol = $protocol;
        $this->reasonPhrase = $reasonPhrase !== ''
            ? $reasonPhrase
            : $this->getDefaultReasonPhrase($status);

        $size = $this->body->getSize();
        if ($size !== null && !$this->hasHeader('Content-Length')) {
            $this->headers['content-length'] = [(string) $size];
        }
    }

    public static function create(): Response
    {
        return new Response();
    }

    public static function text(string $text, int $status = 200): Response
    {
        return Response::create()
            ->withText($text)
            ->withStatus($status);
    }

    public static function html(string $html, int $status = 200): Response
    {
        return Response::create()
            ->withHtml($html)
            ->withStatus($status);
    }

    public static function json(mixed $data, int $status = 200): Response
    {
        return Response::create()
            ->withJson($data)
            ->withStatus($status);
    }

    /**
     * @param array<string, string|string[]> $headers
     * @return array<lowercase-string, list<string>>
     */
    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $value) {
            $lower = strtolower($name);
            $normalized[$lower] = is_array($value)
                ? array_values(array_map('strval', $value))
                : [$value];
        }

        return $normalized;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version): Response
    {
        $clone = clone $this;
        $clone->protocol = $version;
        return $clone;
    }

    /**
     * @return array<string, list<string>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * @param string $name
     * @return list<string>
     */
    public function getHeader(string $name): array
    {
        $header = strtolower($name);
        return $this->headers[$header] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * @param string $name
     * @param string|string[] $value
     * @return Response
     */
    public function withHeader(string $name, $value): Response
    {
        $clone = clone $this;
        $clone->headers[strtolower($name)] = is_array($value)
            ? array_values(array_map('strval', $value))
            : [$value];

        return $clone;
    }

    /**
     * @param string $name
     * @param string|string[] $value
     * @return Response
     */
    public function withAddedHeader(string $name, $value): Response
    {
        $clone = clone $this;
        $lower = strtolower($name);

        $existing = $clone->headers[$lower] ?? [];
        $existing = array_map('strval', $existing);

        $newValues = is_array($value)
            ? array_values(array_map('strval', $value))
            : [$value];

        $clone->headers[$lower] = [...$existing, ...$newValues];

        return $clone;
    }

    public function withoutHeader($name): Response
    {
        $clone = clone $this;
        unset($clone->headers[strtolower($name)]);
        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): Response
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    public function appendBody(string $chunk): Response
    {
        $clone = clone $this;
        $clone->body->write($chunk);
        return $clone;
    }

    public function withStatus($code, $reasonPhrase = ''): Response
    {
        $clone = clone $this;
        $clone->status = $code;
        $clone->reasonPhrase = $reasonPhrase ?: $this->getDefaultReasonPhrase($code);
        return $clone;
    }

    public function withStatusMessage(string $message): Response
    {
        $clone = clone $this;
        $clone->reasonPhrase = $message;
        return $clone;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function withJson(mixed $data, int $flags = JSON_THROW_ON_ERROR): Response
    {
        $clone = clone $this;

        $json = json_encode($data, $flags);

        $stream = new Stream($json);

        return $clone
            ->withBody($stream)
            ->withHeader('Content-Type', 'application/json');
    }

    public function withHtml(string $html): Response
    {
        return $this
            ->withBody(new Stream($html))
            ->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function withText(string $text): Response
    {
        return $this
            ->withBody(new Stream($text))
            ->withHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    private function getDefaultReasonPhrase(int $code): string
    {
        return match ($code) {
            // I'm maduser, not insanuser
            200 => 'OK',
            // @codeCoverageIgnoreStart
            201 => 'Created',
            204 => 'No Content',
            301 => 'Moved Permanently',
            302 => 'Found',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            // @codeCoverageIgnoreEnd
            404 => 'Not Found',
            // @codeCoverageIgnoreStart
            418 => 'I\'m a teapot',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            // @codeCoverageIgnoreEnd
            default => ''
        };
    }
}
