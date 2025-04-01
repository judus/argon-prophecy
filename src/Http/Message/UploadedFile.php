<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

final class UploadedFile implements UploadedFileInterface
{
    private bool $moved = false;

    public function __construct(
        private readonly StreamInterface $stream,
        private readonly int $size,
        private readonly int $error = \UPLOAD_ERR_OK,
        private readonly ?string $clientFilename = null,
        private readonly ?string $clientMediaType = null
    ) {}

    public function getStream(): StreamInterface
    {
        if ($this->moved) {
            throw new \RuntimeException('Cannot retrieve stream after file has been moved.');
        }

        return $this->stream;
    }

    public function moveTo(string $targetPath): void
    {
        if ($this->moved) {
            throw new \RuntimeException('File has already been moved.');
        }

        if (!is_writable(dirname($targetPath))) {
            throw new \RuntimeException("Target directory is not writable: $targetPath");
        }

        $stream = $this->getStream();
        $stream->rewind();

        $dest = fopen($targetPath, 'wb');
        if ($dest === false) {
            throw new \RuntimeException("Unable to open target path: $targetPath");
        }

        while (!$stream->eof()) {
            fwrite($dest, $stream->read(8192));
        }

        fclose($dest);
        $this->moved = true;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }
}
