<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject;

interface ImageCollectionInterface
{
    /**
     * @param array<string> $images List of image names.
     */
    public function add(string $entity, string $field, array $images): void;

    /**
     * Gets all images in the collection.
     * @return array<string, array<string, array<string>>>
     */
    public function getAll(): array;

    /**
     * Gets images for a specific entity and field.
     * @return array<string> List of image names or an empty array.
     */
    public function get(string $entity, string $field): array;

    public function diff(ImageCollectionInterface $collection): ImageCollectionInterface;
}
