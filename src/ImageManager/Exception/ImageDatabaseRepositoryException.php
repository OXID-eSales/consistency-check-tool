<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Exception;

class ImageDatabaseRepositoryException extends \Exception
{
    public function __construct(string $tableName, string $fieldName)
    {
        $message = sprintf(
            'Failed to fetch images for entity (table: %s, field: %s)',
            $tableName,
            $fieldName
        );

        parent::__construct($message);
    }
}
