<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Utils;

use OxidEsales\ConsistencyCheck\ImageManager\Exception\DirectoryNotFoundException;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;
use Symfony\Component\Finder\Finder;

class FileSystemUtils implements FileSystemUtilsInterface
{
    public function __construct(
        private readonly Finder $finder,
        private readonly PathResolverInterface $pathResolver,
    ) {
    }

    public function directoryExists(string $directoryPath): bool
    {
        return is_dir($this->pathResolver->getAbsolutePath($directoryPath));
    }

    /**
     * @inheritDoc
     */
    public function getFilesInDirectory(string $directoryPath): array
    {
        $finder = clone $this->finder;

        if (!$this->directoryExists($directoryPath)) {
            throw new DirectoryNotFoundException($directoryPath);
        }

        $files = [];
        $finder->files()->in($this->pathResolver->getAbsolutePath($directoryPath));

        foreach ($finder as $file) {
            $files[] = $file->getFilename();
        }

        return $files;
    }
}
