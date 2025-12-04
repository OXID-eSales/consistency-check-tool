<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\WebP;

use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataType;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageUsageCheckerInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Dto\ImageCollectionInterface;
use OxidEsales\Eshop\Core\Config;

class WebPImageUsageChecker implements ImageUsageCheckerInterface
{
    public function __construct(
        private readonly ImageUsageCheckerInterface $originalChecker,
        private readonly Config $config
    ) {
    }

    public function isUsed(ImageDataTypeInterface $image, ImageCollectionInterface $usedImages): bool
    {
        if ($this->originalChecker->isUsed($image, $usedImages)) {
            return true;
        }

        $imageName = $image->getImageName();

        if (!$this->isWebp($imageName)) {
            return false;
        }

        if (!$this->isWebpEnabled()) {
            return false;
        }

        $baseImage = new ImageDataType(
            $image->getFieldName(),
            $this->stripWebp($imageName),
            $image->getDirectory()
        );

        return $this->originalChecker->isUsed($baseImage, $usedImages);
    }

    private function isWebp(string $filename): bool
    {
        return str_ends_with($filename, '.webp');
    }

    private function stripWebp(string $filename): string
    {
        return substr($filename, 0, -5);
    }

    private function isWebpEnabled(): bool
    {
        return (bool) $this->config->getConfigParam('blConvertImagesToWebP');
    }
}
