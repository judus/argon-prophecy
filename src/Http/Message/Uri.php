<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Message;

use Psr\Http\Message\UriInterface;
use InvalidArgumentException;

final class Uri implements UriInterface
{
    private string $scheme = '';
    private string $userInfo = '';
    private string $host = '';
    private ?int $port = null;
    private string $path = '';
    private string $query = '';
    private string $fragment = '';

    public function __construct(string $uri = '')
    {
        if ($uri !== '') {
            $parts = parse_url($uri);

            if ($parts === false) {
                throw new InvalidArgumentException("Malformed URI: $uri");
            }

            $this->scheme = $parts['scheme'] ?? '';
            $this->userInfo = $parts['user'] ?? '';
            if (isset($parts['pass'])) {
                $this->userInfo .= ':' . $parts['pass'];
            }
            $this->host = $parts['host'] ?? '';
            $this->port = $parts['port'] ?? null;
            $this->path = $parts['path'] ?? '';
            $this->query = $parts['query'] ?? '';
            $this->fragment = $parts['fragment'] ?? '';
        }
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        if ($this->host === '') {
            return '';
        }

        $authority = $this->host;

        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withScheme($scheme): self
    {
        $clone = clone $this;
        $clone->scheme = $scheme;
        return $clone;
    }

    public function withUserInfo($user, $password = null): self
    {
        $clone = clone $this;
        $clone->userInfo = $user . ($password !== null ? ':' . $password : '');
        return $clone;
    }

    public function withHost($host): self
    {
        $clone = clone $this;
        $clone->host = $host;
        return $clone;
    }

    public function withPort($port): self
    {
        if ($port !== null && ($port < 1 || $port > 65535)) {
            throw new InvalidArgumentException("Invalid port: $port");
        }

        $clone = clone $this;
        $clone->port = $port;
        return $clone;
    }

    public function withPath($path): self
    {
        $clone = clone $this;
        $clone->path = $path;
        return $clone;
    }

    public function withQuery($query): self
    {
        $clone = clone $this;
        $clone->query = ltrim($query, '?');
        return $clone;
    }

    public function withFragment($fragment): self
    {
        $clone = clone $this;
        $clone->fragment = $fragment;
        return $clone;
    }

    public function __toString(): string
    {
        $uri = '';

        if ($this->scheme !== '') {
            $uri .= $this->scheme . ':';
        }

        if ($this->getAuthority() !== '') {
            $uri .= '//' . $this->getAuthority();
        }

        $uri .= $this->path;

        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }
}
