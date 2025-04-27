<?php

declare(strict_types=1);

namespace Maduser\Argon\Http;

use Maduser\Argon\Contracts\Http\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class ResponseEmitter implements ResponseEmitterInterface
{
    public function emit(ResponseInterface $response): void
    {
        if (!headers_sent()) {
            http_response_code($response->getStatusCode());

            foreach ($response->getHeaders() as $name => $values) {
                if (strtolower((string) $name) === 'content-length') {
                    $size = $response->getBody()->getSize();
                    if ($size !== null) {
                        header("Content-Length: $size", false);
                    }
                    continue;
                }

                foreach ($values as $value) {
                    header("$name: $value", false);
                }
            }
        }

        $this->emitBody($response->getBody());
    }

    private function emitBody(StreamInterface $body): void
    {
        if ($body->isSeekable()) {
            $body->rewind();
        }

        while (!$body->eof()) {
            echo $body->read(8192);
        }
    }
}
