<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Message\Factory;

use Maduser\Argon\Http\Message\UploadedFile;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

use const UPLOAD_ERR_OK;

final class UploadedFileFactory implements UploadedFileFactoryInterface
{
    public function createUploadedFile(
        StreamInterface $stream,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null
    ): UploadedFileInterface {
        if ($size !== null && $size < 0) {
            throw new RuntimeException('Size must be non-negative.');
        }

        return new UploadedFile(
            $stream,
            $size ?? $stream->getSize() ?? 0,
            $error,
            $clientFilename,
            $clientMediaType
        );
    }
}
