<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace ImageManager\Repository;

use OxidEsales\ConsistencyCheck\ImageManager\Entity\EntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Repository\ImageSourceRepositoryInterface;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;
use OxidEsales\ConsistencyCheck\ImageManager\Repository\ImageSourceRepository;

class ImageSourceRepositoryTest extends IntegrationTestCase
{
    #[Test]
    public function attachedImagesReturnsImages(): void
    {
        $fieldName = 'OXTHUMB';
        $image1 = uniqid();
        $image2 = uniqid();

        $queryBuilderFactory = ContainerFacade::get(QueryBuilderFactoryInterface::class);
        $this->insertRecord($queryBuilderFactory, ['OXID' => uniqid(), $fieldName => $image1]);
        $this->insertRecord($queryBuilderFactory, ['OXID' => uniqid(), $fieldName => $image2]);

        $entityStub = $this->createStub(EntityInterface::class);
        $entityStub->expects($this->once())
            ->method('getTable')
            ->willReturn('oxarticles');

        $entityStub
            ->method('getFieldName')
            ->willReturn($fieldName);

        $sut = $this->getSut(queryBuilderFactory: $queryBuilderFactory, entity: $entityStub);

        $this->assertSame([$image1, $image2], $sut->getAttachedImages());
    }

    #[Test]
    public function attachedImagesReturnsEmptyArrayWhenNoRows(): void
    {
        $entityStub = $this->createStub(EntityInterface::class);
        $entityStub
            ->method('getTable')
            ->willReturn('oxarticles');

        $entityStub
            ->method('getFieldName')
            ->willReturn('OXPIC1');

        $sut = $this->getSut(entity: $entityStub);

        $this->assertSame([], $sut->getAttachedImages());
    }

    private function insertRecord(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        array $fields
    ): void {
        $queryBuilder = $queryBuilderFactory->create();
        $queryBuilder->insert('oxarticles');

        foreach ($fields as $column => $value) {
            $queryBuilder->setValue($column, ":{$column}")
                ->setParameter(":{$column}", $value);
        }

        $queryBuilder->execute();
    }

    private function getSut(
        ?QueryBuilderFactoryInterface $queryBuilderFactory = null,
        ?EntityInterface $entity = null
    ): ImageSourceRepositoryInterface {
        $entity ??= $this->createStub(EntityInterface::class);
        $queryBuilderFactory ??= ContainerFacade::get(QueryBuilderFactoryInterface::class);
        return new ImageSourceRepository(
            queryBuilderFactory: $queryBuilderFactory,
            entity: $entity,
        );
    }
}
