<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Repository;

use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Exception\ImageDatabaseRepositoryException;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageDataTypeFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class ImageDatabaseRepository implements ImageDatabaseRepositoryInterface
{
    public function __construct(
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory,
        private readonly ImageDataTypeFactoryInterface $imageDataTypeFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getImages(ImageEntityInterface $entity): array
    {
        $queryBuilder = $this->queryBuilderFactory->create();

        try {
            $queryBuilder->select($entity->getFieldName())
                ->from($entity->getTable())
                ->where(
                    $queryBuilder->expr()->isNotNull($entity->getFieldName())
                );

            $queryResult = $queryBuilder->execute();

            $images = [];
            while ($data = $queryResult->fetchAssociative()) {
                $images[] = $this->imageDataTypeFactory->createFromFileDetails(
                    fieldName: $entity->getFieldName(),
                    imageName: $data[$entity->getFieldName()],
                    directory: $entity->getDirectory(),
                );
            }

            return $images;
        } catch (\Exception $e) {
            throw new ImageDatabaseRepositoryException($entity->getTable(), $entity->getFieldName());
        }
    }
}
