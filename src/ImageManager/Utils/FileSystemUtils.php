<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Utils;

use OxidEsales\ConsistencyCheck\ImageManager\Exception\FileSystemException;
use Symfony\Component\Finder\Finder;

class FileSystemUtils implements FileSystemUtilsInterface
{
    public function __construct(
        private readonly Finder $finder
    ) {
    }

    public function directoryExists(string $directoryPath): bool
    {
        return is_dir($directoryPath);
    }

    /**
     * @inheritDoc
     */
    public function getFilesInDirectory(string $directoryPath): array
    {
        if (!$this->directoryExists($directoryPath)) {
            throw FileSystemException::directoryNotFound($directoryPath);
        }

        $files = [];
        $this->finder->files()->in($directoryPath);

        foreach ($this->finder as $file) {
            $files[] = $file->getFilename();
        }

        return $files;
    }

    /**
     * @inheritDoc
     */
    public function moveFile(string $source, string $destination): void
    {
        $this->ensureFileExists($source);

        if (!@rename($source, $destination)) {
            throw FileSystemException::fileMoveFailed($source, $destination);
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteFile(string $filePath): void
    {
        $this->ensureFileExists($filePath);

        if (!@unlink($filePath)) {
            if (file_exists($filePath)) {
                throw FileSystemException::fileDeleteFailed($filePath);
            }
        }
    }

    /**
     * @throws FileSystemException If the file does not exist.
     */
    private function ensureFileExists(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw FileSystemException::fileNotFound($filePath);
        }
    }
}
