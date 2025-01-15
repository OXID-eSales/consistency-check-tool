<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageCollectionFactoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Repository\ImageRepositoryInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class ImageCheckerService implements ImageCheckerServiceInterface
{
    public function __construct(
        private readonly ImageRepositoryInterface $imageDatabaseRepository,
        private readonly ImageRepositoryInterface $imageDirectoryRepository,
        private readonly ImageCollectionFactoryInterface $imageCollectionFactory,
        private readonly PsrLoggerInterface $logger,
    ) {
    }

    public function getUnusedImages(ImageEntityInterface $entity): ImageCollectionInterface
    {
        try {
            $databaseImageCollection = $this->imageDatabaseRepository->getImages($entity);
            $directoryImageCollection = $this->imageDirectoryRepository->getImages($entity);

            $unusedImages = $this->imageCollectionFactory->create();

            foreach ($directoryImageCollection->getAll() as $directoryImage) {
                if (!$databaseImageCollection->contains($directoryImage)) {
                    $unusedImages->add($directoryImage);
                }
            }

            return $unusedImages;
        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                'Error processing entity %s (table: %s): %s',
                $entity->getName(),
                $entity->getTable(),
                $e->getMessage()
            ));

            return $this->imageCollectionFactory->create();
        }
    }
}
