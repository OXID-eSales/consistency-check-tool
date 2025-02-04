<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Repository;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageCollectionFactoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageDataTypeFactoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Utils\FileSystemUtilsInterface;

class ImageDirectoryRepository implements ImageRepositoryInterface
{
    public function __construct(
        private readonly FileSystemUtilsInterface $fileSystemUtils,
        private readonly ImageDataTypeFactoryInterface $imageFactory,
        private readonly ImageCollectionFactoryInterface $imageCollectionFactory,
    ) {
    }

    public function getImages(ImageEntityInterface $entity): ImageCollectionInterface
    {
        $directoryPath = $entity->getDirectory();
        $imageCollection = $this->imageCollectionFactory->create();

        if (!$this->fileSystemUtils->directoryExists($directoryPath)) {
            return $imageCollection;
        }

        $files = $this->fileSystemUtils->getFilesInDirectory($directoryPath);

        foreach ($files as $fileName) {
            $imageCollection->add(
                $this->imageFactory->createFromFileDetails(
                    $entity->getFieldName(),
                    $fileName,
                    $directoryPath,
                )
            );
        }

        return $imageCollection;
    }
}
