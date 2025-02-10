<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Service;

use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageEntityFilterService;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageEntityFilterServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ImageEntityFilterServiceTest extends TestCase
{
    #[Test]
    public function itReturnsAllEntitiesWhenTypeIsNotProvided(): void
    {
        $sut = $this->getSut();
        $expectedEntities = [
            $this->createEntityStub(uniqid()),
            $this->createEntityStub(uniqid())
        ];

        $actualEntities = $sut->filterEntitiesByName($expectedEntities);

        $this->assertCount(2, $actualEntities);
        $this->assertSame($expectedEntities, $actualEntities);
    }

    #[Test]
    public function itFiltersEntitiesByName(): void
    {
        $sut = $this->getSut();
        $entities = [
            $expectedSingleEntity = $this->createEntityStub($entityName = uniqid()),
            $this->createEntityStub(uniqid())
        ];

        $actualEntities = $sut->filterEntitiesByName($entities, $entityName);

        $this->assertCount(1, $actualEntities);
        $this->assertContains($expectedSingleEntity, $actualEntities);
    }

    private function createEntityStub(string $type): ImageEntityInterface
    {
        $entityStub = $this->createStub(ImageEntityInterface::class);
        $entityStub->method('getName')->willReturn($type);
        return $entityStub;
    }

    public function getSut(): ImageEntityFilterServiceInterface
    {
        return new ImageEntityFilterService();
    }
}
