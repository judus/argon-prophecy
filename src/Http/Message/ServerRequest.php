<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * @psalm-type HeaderInputMap = array<string, string|string[]>
 * @psalm-type HeaderMap = array<lowercase-string, list<string>>
 */
final class ServerRequest implements ServerRequestInterface
{
    private string $method;
    private StreamInterface $body;
    private UriInterface $uri;
    private string $requestTarget = '';

    /** @var HeaderMap */
    private array $headers;

    /**
     * @param HeaderInputMap $headers
     */
    public function __construct(
        ?string $method = null,
        ?UriInterface $uri = null,
        array $headers = [],
        ?StreamInterface $body = null,
        private string $protocol = '1.1',
        private array $serverParams = [],
        private array $cookieParams = [],
        private array $queryParams = [],
        private array $uploadedFiles = [],
        private null|array|object $parsedBody = null,
        private array $attributes = [],
    ) {
        $this->headers = $this->normalizeHeaders($headers);
        $this->method = strtoupper($method ?? $_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri = $uri ?? new Uri('');
        $this->body = $body ?? new Stream(fopen('php://temp', 'r+'));
    }

    /**
     * @param HeaderInputMap $headers
     * @return HeaderMap
     */
    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $value) {
            $lower = strtolower($name);

            if (is_array($value)) {
                $normalized[$lower] = array_values(array_map('strval', $value));
            } else {
                $normalized[$lower] = [strval($value)];
            }
        }

        return $normalized;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version): self
    {
        $clone = clone $this;
        $clone->protocol = $version;
        return $clone;
    }

    /**
     * @return HeaderMap
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * @param string $name
     * @return list<string>
     */
    public function getHeader(string $name): array
    {
        return $this->headers[strtolower($name)] ?? [];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * @param string $name
     * @param string|string[] $value
     * @return ServerRequest
     */
    public function withHeader(string $name, $value): self
    {
        $clone = clone $this;
        $clone->headers[strtolower($name)] = is_array($value)
            ? array_values(array_map('strval', $value))
            : [strval($value)];
        return $clone;
    }

    /**
     * @param string $name
     * @param string|string[] $value
     * @return ServerRequest
     */
    public function withAddedHeader(string $name, $value): self
    {
        $clone = clone $this;
        $lower = strtolower($name);

        $existing = $clone->headers[$lower] ?? [];
        $newValues = is_array($value)
            ? array_values(array_map('strval', $value))
            : [strval($value)];

        $clone->headers[$lower] = array_merge($existing, $newValues);
        return $clone;
    }

    public function withoutHeader(string $name): self
    {
        $clone = clone $this;
        unset($clone->headers[strtolower($name)]);
        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): self
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    public function getRequestTarget(): string
    {
        return $this->requestTarget !== ''
            ? $this->requestTarget
            : ($this->uri->getPath() . ($this->uri->getQuery() ? '?' . $this->uri->getQuery() : ''));
    }

    public function withRequestTarget(string $requestTarget): self
    {
        $clone = clone $this;
        $clone->requestTarget = $requestTarget;
        return $clone;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): self
    {
        $clone = clone $this;
        $clone->method = strtoupper($method);
        return $clone;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): self
    {
        $clone = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost && $uri->getHost() !== '') {
            $clone->headers['host'] = [$uri->getHost()];
        }

        return $clone;
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies): self
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;
        return $clone;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): self
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): self
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    public function getParsedBody(): null|array|object
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data): self
    {
        $clone = clone $this;
        $clone->parsedBody = $data;
        return $clone;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute(string $name, mixed $value): self
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    public function withoutAttribute(string $name): self
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }
}
