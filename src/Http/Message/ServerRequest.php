<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class ServerRequest implements ServerRequestInterface
{
    private string $method;
    private StreamInterface $body;
    private UriInterface $uri;
    private string $requestTarget = '';

    public function __construct(
        ?string $method = null,
        ?UriInterface $uri = null,
        private array $headers = [],
        ?StreamInterface $body = null,
        private string $protocol = '1.1',
        private array $serverParams = [],
        private array $cookieParams = [],
        private array $queryParams = [],
        private array $uploadedFiles = [],
        private null|array|object $parsedBody = null,
        private array $attributes = [],
    ) {
        $this->method = strtoupper($method ?? $_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->body = $body ?? new Stream(fopen('php://temp', 'r+'));
        $this->uri = $uri ?? new Uri('');
        $this->headers = array_change_key_case($headers, CASE_LOWER);
    }

    /** @inheritdoc */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /** @inheritdoc */
    public function withProtocolVersion($version): self
    {
        $clone = clone $this;
        $clone->protocol = $version;
        return $clone;
    }

    /** @inheritdoc */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /** @inheritdoc */
    public function hasHeader($name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /** @inheritdoc */
    public function getHeader($name): array
    {
        return $this->headers[strtolower($name)] ?? [];
    }

    /** @inheritdoc */
    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /** @inheritdoc */
    public function withHeader($name, $value): self
    {
        $clone = clone $this;
        $clone->headers[strtolower($name)] = (array)$value;
        return $clone;
    }

    /** @inheritdoc */
    public function withAddedHeader($name, $value): self
    {
        $clone = clone $this;
        $lower = strtolower($name);
        $clone->headers[$lower] = array_merge($clone->headers[$lower] ?? [], (array)$value);
        return $clone;
    }

    /** @inheritdoc */
    public function withoutHeader($name): self
    {
        $clone = clone $this;
        unset($clone->headers[strtolower($name)]);
        return $clone;
    }

    /** @inheritdoc */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /** @inheritdoc */
    public function withBody(StreamInterface $body): self
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    /** @inheritdoc */
    public function getRequestTarget(): string
    {
        return $this->requestTarget !== ''
            ? $this->requestTarget
            : ($this->uri->getPath() . ($this->uri->getQuery() ? '?' . $this->uri->getQuery() : ''));
    }

    /** @inheritdoc */
    public function withRequestTarget($requestTarget): self
    {
        if (!is_string($requestTarget)) {
            throw new InvalidArgumentException('Request target must be a string');
        }

        $clone = clone $this;
        $clone->requestTarget = $requestTarget;
        return $clone;
    }

    /** @inheritdoc */
    public function getMethod(): string
    {
        return $this->method;
    }

    /** @inheritdoc */
    public function withMethod($method): self
    {
        $clone = clone $this;
        $clone->method = strtoupper($method);
        return $clone;
    }

    /** @inheritdoc */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /** @inheritdoc */
    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $clone = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost) {
            $host = $uri->getHost();
            if ($host !== '') {
                $clone->headers['host'] = [$host];
            }
        }

        return $clone;
    }

    /** @inheritdoc */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /** @inheritdoc */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /** @inheritdoc */
    public function withCookieParams(array $cookies): self
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;
        return $clone;
    }

    /** @inheritdoc */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /** @inheritdoc */
    public function withQueryParams(array $query): self
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    /** @inheritdoc */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /** @inheritdoc */
    public function withUploadedFiles(array $uploadedFiles): self
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    /** @inheritdoc */
    public function getParsedBody(): null|array|object
    {
        return $this->parsedBody;
    }

    /** @inheritdoc */
    public function withParsedBody($data): self
    {
        if (!is_array($data) && !is_object($data) && $data !== null) {
            throw new InvalidArgumentException('Parsed body must be array|object|null');
        }

        $clone = clone $this;
        $clone->parsedBody = $data;
        return $clone;
    }

    /** @inheritdoc */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /** @inheritdoc */
    public function getAttribute($name, $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    /** @inheritdoc */
    public function withAttribute($name, $value): self
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    /** @inheritdoc */
    public function withoutAttribute($name): self
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }
}
