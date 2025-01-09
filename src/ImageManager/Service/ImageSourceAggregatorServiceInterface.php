<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;

interface ImageSourceAggregatorServiceInterface
{
    /**
     * Fetches all attached images from the repositories and structures them into an ImageCollection.
     *
     * @return ImageCollectionInterface Structured collection of attached images.
     */
    public function getAttachedImages(): ImageCollectionInterface;
}
