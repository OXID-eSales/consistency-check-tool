<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace ImageManager\Factory;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageCollectionFactory;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageCollectionFactoryInterface;
use OxidEsales\Eshop\Core\Config;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ImageCollectionFactoryTest extends TestCase
{
    #[Test]
    public function itCreatesImageCollection(): void
    {
        $imageCollectionFactory1 = $this->getSut();
        $imageCollectionFactory2 = $this->getSut();

        $collection1 = $imageCollectionFactory1->create();
        $collection2 = $imageCollectionFactory2->create();

        $this->assertInstanceOf(ImageCollectionInterface::class, $collection1);
        $this->assertNotSame($collection1, $collection2);
    }

    public function getSut(): ImageCollectionFactoryInterface
    {
        return new ImageCollectionFactory(
            $this->createStub(Config::class),
        );
    }
}
