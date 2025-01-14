<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Exception;

class FileSystemException extends \RuntimeException
{
    public const DIRECTORY_NOT_FOUND = 'Directory not found: %s';
    public const FILE_NOT_FOUND = 'File not found: %s';
    public const FILE_MOVE_FAILED = 'Failed to move file from %s to %s';
    public const FILE_DELETE_FAILED = 'Failed to delete file: %s';

    public static function directoryNotFound(string $directoryPath): self
    {
        return new self(sprintf(self::DIRECTORY_NOT_FOUND, $directoryPath));
    }

    public static function fileNotFound(string $filePath): self
    {
        return new self(sprintf(self::FILE_NOT_FOUND, $filePath));
    }

    public static function fileMoveFailed(string $source, string $destination): self
    {
        return new self(sprintf(self::FILE_MOVE_FAILED, $source, $destination));
    }

    public static function fileDeleteFailed(string $filePath): self
    {
        return new self(sprintf(self::FILE_DELETE_FAILED, $filePath));
    }
}
