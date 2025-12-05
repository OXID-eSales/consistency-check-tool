<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Service;

interface PathResolverInterface
{
    public function getAbsolutePath(string $path): string;
}
