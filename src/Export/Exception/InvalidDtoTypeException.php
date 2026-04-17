<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Exception;

use Exception;
use OxidEsales\ConsistencyCheck\Export\Dto\ExportableDtoInterface;

final class InvalidDtoTypeException extends Exception
{
    public function __construct(ExportableDtoInterface $dto, string $expectedType)
    {
        $message = sprintf(
            'Invalid DTO type provided. Expected %s, got %s',
            $expectedType,
            get_class($dto)
        );

        parent::__construct($message);
    }
}
