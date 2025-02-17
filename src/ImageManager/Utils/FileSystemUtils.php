<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Utils;

use OxidEsales\ConsistencyCheck\ImageManager\Exception\DirectoryNotFoundException;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

class FileSystemUtils implements FileSystemUtilsInterface
{
    public function __construct(
        private readonly Finder $finder,
        private readonly ContextInterface $context,
    ) {
    }

    public function directoryExists(string $directoryPath): bool
    {
        return is_dir($this->getAbsolutePath($directoryPath));
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
        $finder->files()->in($this->getAbsolutePath($directoryPath));

        foreach ($finder as $file) {
            $files[] = $file->getFilename();
        }

        return $files;
    }

    public function getAbsolutePath(string $path): string
    {
        return Path::join($this->context->getSourcePath(), $path);
    }
}
