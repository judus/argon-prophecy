<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

final class ServerRequest implements ServerRequestInterface
{
    private string $method;
    private Stream $body;
    private UriInterface $uri;

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
        $this->method = $method ?? $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->body = new Stream(fopen('php://temp', 'r+'));
        $this->uri = $uri ?? new Uri('');
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
        $name = strtolower($name);
        return $this->headers[$name] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader($name, $value): self
    {
        $clone = clone $this;
        $clone->headers[strtolower($name)] = (array)$value;
        return $clone;
    }

    public function withAddedHeader($name, $value): self
    {
        $clone = clone $this;
        $name = strtolower($name);
        if (!isset($clone->headers[$name])) {
            $clone->headers[$name] = [];
        }

        $clone->headers[$name] = array_merge($clone->headers[$name], (array)$value);
        return $clone;
    }

    public function withoutHeader($name): self
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
        return $this->uri->getPath() . ($this->uri->getQuery() ? '?' . $this->uri->getQuery() : '');
    }

    public function withRequestTarget($requestTarget): self
    {
        throw new \LogicException('Not implemented: withRequestTarget');
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod($method): self
    {
        $clone = clone $this;
        $clone->method = strtoupper($method);
        return $clone;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $clone = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost) {
            $host = $uri->getHost();
            if ($host) {
                $clone->headers['host'] = [$host];
            }
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
        if (!is_array($data) && !is_object($data) && $data !== null) {
            throw new InvalidArgumentException('Parsed body must be array|object|null');
        }

        $clone = clone $this;
        $clone->parsedBody = $data;
        return $clone;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute($name, $value): self
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    public function withoutAttribute($name): self
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }
}
