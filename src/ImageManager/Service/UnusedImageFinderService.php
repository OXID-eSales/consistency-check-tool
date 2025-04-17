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

class UnusedImageFinderService implements UnusedImageFinderServiceInterface
{
    public function __construct(
        private readonly ImageRepositoryInterface $imageDatabaseRepository,
        private readonly ImageRepositoryInterface $imageDirectoryRepository,
        private readonly ImageCollectionFactoryInterface $imageCollectionFactory,
        private readonly ImageUsageCheckerInterface $imageUsageChecker,
        private readonly PsrLoggerInterface $logger,
    ) {
    }

    public function getUnusedImages(ImageEntityInterface $entity): ImageCollectionInterface
    {
        try {
            $usedImageCollection = $this->imageDatabaseRepository->getImages($entity);
            $allImageCollection = $this->imageDirectoryRepository->getImages($entity);

            $unusedImages = $this->imageCollectionFactory->create();

            foreach ($allImageCollection->getAll() as $image) {
                if (!$this->imageUsageChecker->isUsed($image, $usedImageCollection)) {
                    $unusedImages->add($image);
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
