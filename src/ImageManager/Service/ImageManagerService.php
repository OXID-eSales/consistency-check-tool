<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Utils\FileSystemUtilsInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class ImageManagerService implements ImageManagerServiceInterface
{
    private const IMAGE_MOVED_SUCCESSFUL = 'Moved image: %s to %s';
    private const IMAGE_MOVED_DRY_RUN = '[DRY-RUN] Move %s to %s';
    private const IMAGE_MOVED_FAILED = 'Failed to move image: %s. Error: %s';
    private const IMAGE_DELETED_SUCCESSFUL = 'Deleted image: %s';
    private const IMAGE_DELETED_DRY_RUN = '[DRY-RUN] Delete %s';
    private const IMAGE_DELETED_FAILED = 'Failed to delete image: %s';

    public function __construct(
        private readonly FileSystemUtilsInterface $fileSystemUtils,
        private readonly PsrLoggerInterface $logger
    ) {
    }
    public function moveImages(ImageCollectionInterface $images, string $destination, bool $dryRun = false): void
    {
        foreach ($images->getAll() as $image) {
            $sourcePath = rtrim($image->getDirectory(), '/') . '/' . $image->getImageName();
            $destinationPath = rtrim($destination, '/') . '/' . $image->getImageName();

            if ($dryRun) {
                $this->logger->info(sprintf(self::IMAGE_MOVED_DRY_RUN, $sourcePath, $destinationPath));
            } else {
                try {
                    $this->fileSystemUtils->moveFile($sourcePath, $destinationPath);
                    $this->logger->info(sprintf(self::IMAGE_MOVED_SUCCESSFUL, $sourcePath, $destinationPath));
                } catch (\Exception $e) {
                    $this->logger->error(sprintf(self::IMAGE_MOVED_FAILED, $sourcePath, $e->getMessage()));
                }
            }
        }
    }

    public function deleteImages(ImageCollectionInterface $images, bool $dryRun = false): void
    {
        foreach ($images->getAll() as $image) {
            $filePath = rtrim($image->getDirectory(), '/') . '/' . $image->getImageName();

            if ($dryRun) {
                $this->logger->info(sprintf(self::IMAGE_DELETED_DRY_RUN, $filePath));
            } else {
                try {
                    $this->fileSystemUtils->deleteFile($filePath);
                    $this->logger->info(sprintf(self::IMAGE_DELETED_SUCCESSFUL, $filePath));
                } catch (\Exception $e) {
                    $this->logger->error(sprintf(self::IMAGE_DELETED_FAILED, $filePath, $e->getMessage()));
                }
            }
        }
    }
}
