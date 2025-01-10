<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Repository;

use OxidEsales\ConsistencyCheck\ImageManager\Entity\EntityInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class ImageSourceRepository implements ImageSourceRepositoryInterface
{
    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory,
        private EntityInterface $entity
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAttachedImages(): array
    {
        $queryBuilder = $this->queryBuilderFactory->create();

        $queryBuilder->select($this->entity->getFieldName())
            ->from($this->entity->getTable())
            ->where("{$this->entity->getFieldName()} IS NOT NULL");

        return $queryBuilder->execute()->fetchFirstColumn();
    }
}
