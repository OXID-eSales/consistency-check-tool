<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Repository;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Result;
use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Exception\ImageDatabaseRepositoryException;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageCollectionFactoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageDataTypeFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class ImageDatabaseRepository implements ImageRepositoryInterface
{
    public function __construct(
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory,
        private readonly ImageDataTypeFactoryInterface $imageDataTypeFactory,
        private readonly ImageCollectionFactoryInterface $imageCollectionFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getImages(ImageEntityInterface $entity): ImageCollectionInterface
    {
        $queryBuilder = $this->queryBuilderFactory->create();

        try {
            $queryBuilder->select($entity->getFieldName())
                ->from($entity->getTable())
                ->where(
                    $queryBuilder->expr()->isNotNull($entity->getFieldName())
                );

            /** @var Result $queryResult */
            $queryResult = $queryBuilder->execute();

            $imageCollection = $this->imageCollectionFactory->create();
            while ($data = $queryResult->fetchAssociative()) {
                $imageCollection->add(
                    $this->imageDataTypeFactory->createFromFileDetails(
                        fieldName: $entity->getFieldName(),
                        imageName: $data[$entity->getFieldName()],
                        directory: $entity->getDirectory(),
                    )
                );
            }

            return $imageCollection;
        } catch (DBALException) {
            throw new ImageDatabaseRepositoryException($entity->getTable(), $entity->getFieldName());
        }
    }
}
