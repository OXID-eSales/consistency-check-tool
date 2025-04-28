<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

interface LogReaderInterface
{
    /**
     * @return string[]
     */
    public function readLines(): array;
}
