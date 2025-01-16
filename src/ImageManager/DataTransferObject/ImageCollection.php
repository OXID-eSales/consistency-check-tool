<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject;

use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;

class ImageCollection implements ImageCollectionInterface
{
    /**
     * @var array<string, ImageDataTypeInterface>
     */
    private array $imageHashMap = [];

    /**
     * @inheritDoc
     */
    public function add(ImageDataTypeInterface $image): void
    {
        $hash = $this->hashImage($image);
        $this->imageHashMap[$hash] = $image;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        return $this->imageHashMap;
    }

    public function contains(ImageDataTypeInterface $image): bool
    {
        $hash = $this->hashImage($image);
        return isset($this->imageHashMap[$hash]);
    }

    private function hashImage(ImageDataTypeInterface $image): string
    {
        return md5($image->getFieldName() . $image->getImageName() . $image->getDirectory());
    }
}
