<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Message;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class Response implements ResponseInterface
{
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
        $this->headers = $this->normalizeHeaders($headers);
        $this->protocol = $protocol;
        $this->reasonPhrase = $reasonPhrase;
    }

    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $normalized[strtolower($name)] = (array)$value;
        }
        return $normalized;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version): static
    {
        $clone = clone $this;
        $clone->protocol = $version;
        return $clone;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function getHeader($name): array
    {
        return $this->headers[strtolower($name)] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader($name, $value): static
    {
        $clone = clone $this;
        $clone->headers[strtolower($name)] = (array)$value;
        return $clone;
    }

    public function withAddedHeader($name, $value): static
    {
        $clone = clone $this;
        $lower = strtolower($name);
        $clone->headers[$lower] = array_merge($clone->headers[$lower] ?? [], (array)$value);
        return $clone;
    }

    public function withoutHeader($name): static
    {
        $clone = clone $this;
        unset($clone->headers[strtolower($name)]);
        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): static
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function withStatus($code, $reasonPhrase = ''): static
    {
        $clone = clone $this;
        $clone->status = $code;
        $clone->reasonPhrase = $reasonPhrase ?: $this->getDefaultReasonPhrase($code);
        return $clone;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    private function getDefaultReasonPhrase(int $code): string
    {
        return match ($code) {
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            301 => 'Moved Permanently',
            302 => 'Found',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            default => ''
        };
    }
}
