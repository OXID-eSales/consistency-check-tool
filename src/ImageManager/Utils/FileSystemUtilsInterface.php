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
     * @return array<string>
     * @throws FileSystemException
     */
    public function getFilesInDirectory(string $directoryPath): array;
}
