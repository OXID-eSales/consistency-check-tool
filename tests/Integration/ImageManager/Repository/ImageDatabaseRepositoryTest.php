<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace ImageManager\Repository;

use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Exception\ImageDatabaseRepositoryException;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageDataTypeFactoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Repository\ImageDatabaseRepositoryInterface;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;
use OxidEsales\ConsistencyCheck\ImageManager\Repository\ImageDatabaseRepository;

class ImageDatabaseRepositoryTest extends IntegrationTestCase
{
    #[Test]
    public function itReturnsAllImagesAsImageDataTypeObjects(): void
    {
        $fieldName = 'OXTHUMB';
        $image1 = uniqid();
        $image2 = uniqid();
        $directoryPath = uniqid();

        $entityStub = $this->createEntityStub($fieldName, 'oxarticles', $directoryPath);

        $queryBuilderFactory = ContainerFacade::get(QueryBuilderFactoryInterface::class);
        $this->insertRecord($queryBuilderFactory, ['OXID' => uniqid(), $fieldName => $image1]);
        $this->insertRecord($queryBuilderFactory, ['OXID' => uniqid(), $fieldName => $image2]);

        $imageDataTypeFactoryMock = $this->createMock(ImageDataTypeFactoryInterface::class);
        $imageDataTypeFactoryMock
            ->method('createFromFileDetails')
            ->willReturnMap([
                [$fieldName, $image1, $directoryPath, $this->createStub(ImageDataTypeInterface::class)],
                [$fieldName, $image2, $directoryPath, $this->createStub(ImageDataTypeInterface::class)],
            ]);

        $sut = $this->getSut(
            queryBuilderFactory: $queryBuilderFactory,
            imageDataTypeFactory: $imageDataTypeFactoryMock
        );

        $images = $sut->getImages(entity: $entityStub);

        $this->assertCount(2, $images);
        $this->assertContainsOnlyInstancesOf(ImageDataTypeInterface::class, $images);
    }

    #[Test]
    public function getImagesReturnsEmptyArrayWhenNoRows(): void
    {
        $entityStub = $this->createEntityStub('OXPIC1', 'oxarticles');

        $sut = $this->getSut();
        $sut->getImages(entity: $entityStub);

        $this->assertSame([], $sut->getImages($entityStub));
    }

    #[Test]
    public function itThrowsImageDatabaseRepositoryExceptionOnQueryFailure(): void
    {
        $this->expectException(ImageDatabaseRepositoryException::class);

        $sut = $this->getSut();
        $sut->getImages(entity: $this->createEntityStub());
    }

    private function createEntityStub(
        ?string $filedName = null,
        ?string $tableName = null,
        ?string $directory = null,
    ): ImageEntityInterface {
        $entityStub = $this->createStub(ImageEntityInterface::class);
        $entityStub->method('getFieldName')->willReturn($filedName ??= uniqid());
        $entityStub->method('getTable')->willReturn($tableName ??= uniqid());
        $entityStub->method('getDirectory')->willReturn($directory ??= uniqid());

        return $entityStub;
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
        ?ImageDataTypeFactoryInterface $imageDataTypeFactory = null,
    ): ImageDatabaseRepositoryInterface {
        $queryBuilderFactory ??= ContainerFacade::get(QueryBuilderFactoryInterface::class);
        $imageDataTypeFactory ??= $this->createStub(ImageDataTypeFactoryInterface::class);
        return new ImageDatabaseRepository(
            queryBuilderFactory: $queryBuilderFactory,
            imageDataTypeFactory: $imageDataTypeFactory,
        );
    }
}
