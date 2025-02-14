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
use OxidEsales\EshopCommunity\Internal\Framework\FileSystem\ImageHandlerInterface;

class ImageManagerService implements ImageManagerServiceInterface
{
    private const IMAGE_MOVED_SUCCESSFUL = 'Moved image: %s to %s for %s';
    private const IMAGE_MOVED_DRY_RUN = '[DRY-RUN] Move %s to %s for %s';
    private const IMAGE_MOVED_FAILED = 'Failed to move image: %s. Error: %s for %s';
    private const IMAGE_DELETED_SUCCESSFUL = 'Deleted image: %s for %s';
    private const IMAGE_DELETED_DRY_RUN = '[DRY-RUN] Delete %s for %s';
    private const IMAGE_DELETED_FAILED = 'Failed to delete image: %s. Error: %s for %s';

    public function __construct(
        private readonly FileSystemUtilsInterface $fileSystemUtils,
        private readonly PsrLoggerInterface $logger,
        private readonly ImageHandlerInterface $imageHandler,
    ) {
    }

    public function moveImages(ImageCollectionInterface $images, string $destination, bool $dryRun = false): int
    {
        $moveCount = 0;

        foreach ($images->getAll() as $image) {
            $sourcePath = rtrim($image->getDirectory(), '/') . '/' . $image->getImageName();
            $destinationPath = rtrim($destination, '/') . rtrim($image->getDirectory(), '/') . '/' . $image->getImageName();

            $entityDetails = sprintf('[%s:%s]', $image->getFieldName(), $image->getImageName());

            if ($dryRun) {
                $this->logger->info(sprintf(self::IMAGE_MOVED_DRY_RUN, $sourcePath, $destinationPath, $entityDetails));
                $moveCount++;
            } else {
                try {
                    $this->imageHandler->copy($this->fileSystemUtils->getAbsolutePath($sourcePath), $destinationPath);
                    $this->imageHandler->remove($sourcePath);
                    $moveCount++;
                    $this->logger->info(
                        sprintf(
                            self::IMAGE_MOVED_SUCCESSFUL,
                            $sourcePath,
                            $destinationPath,
                            $entityDetails
                        )
                    );
                } catch (\Exception $e) {
                    $this->logger->error(
                        sprintf(
                            self::IMAGE_MOVED_FAILED,
                            $sourcePath,
                            $e->getMessage(),
                            $entityDetails
                        )
                    );
                }
            }
        }

        return $moveCount;
    }

    public function deleteImages(ImageCollectionInterface $images, bool $dryRun = false): int
    {
        $deletedCount = 0;

        foreach ($images->getAll() as $image) {
            $filePath = rtrim($image->getDirectory(), '/') . '/' . $image->getImageName();

            $entityDetails = sprintf('[%s:%s]', $image->getFieldName(), $image->getImageName());

            if ($dryRun) {
                $this->logger->info(sprintf(self::IMAGE_DELETED_DRY_RUN, $filePath, $entityDetails));
                $deletedCount++;
            } else {
                try {
                    $this->imageHandler->remove($filePath);
                    $deletedCount++;
                    $this->logger->info(
                        sprintf(
                            self::IMAGE_DELETED_SUCCESSFUL,
                            $filePath,
                            $entityDetails
                        )
                    );
                } catch (\Exception $e) {
                    $this->logger->error(
                        sprintf(
                            self::IMAGE_DELETED_FAILED,
                            $filePath,
                            $e->getMessage(),
                            $entityDetails
                        )
                    );
                }
            }
        }

        return $deletedCount;
    }
}
