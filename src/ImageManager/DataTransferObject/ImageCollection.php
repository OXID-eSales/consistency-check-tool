<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject;

use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataType;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;
use OxidEsales\Eshop\Core\Config;

class ImageCollection implements ImageCollectionInterface
{
    /**
     * @var array<string, ImageDataTypeInterface>
     */
    private array $imageHashMap = [];

    public function __construct(
        private readonly Config $config
    ) {
    }

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

    public function containsOriginalForWebP(ImageDataTypeInterface $webp): bool
    {
        if ($this->isWebPEnabled() && str_ends_with($webp->getImageName(), '.webp')) {
            $originalImage = new ImageDataType(
                fieldName: $webp->getFieldName(),
                imageName: str_replace('.webp', '', $webp->getImageName()),
                directory: $webp->getDirectory()
            );
            return $this->contains($originalImage);
        }

        return false;
    }

    private function isWebPEnabled(): bool
    {
        return (bool) $this->config->getConfigParam('blConvertImagesToWebP');
    }

    private function hashImage(ImageDataTypeInterface $image): string
    {
        return md5($image->getFieldName() . $image->getImageName() . $image->getDirectory());
    }
}
