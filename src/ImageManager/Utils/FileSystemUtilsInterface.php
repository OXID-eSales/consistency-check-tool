<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Utils;

use OxidEsales\ConsistencyCheck\ImageManager\Exception\FileSystemException;

interface FileSystemUtilsInterface
{
    public function directoryExists(string $directoryPath): bool;

    /**
     * @return array<string> Array of filenames.
     * @throws FileSystemException If the directory does not exist.
     */
    public function getFilesInDirectory(string $directoryPath): array;

    /**
     * @throws FileSystemException If the source file does not exist or the move fails.
     */
    public function moveFile(string $source, string $destination): void;

    /**
     * @throws FileSystemException If the file does not exist or the delete operation fails.
     */
    public function deleteFile(string $filePath): void;
}
