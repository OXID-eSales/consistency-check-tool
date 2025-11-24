<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Service;

interface PathResolverInterface
{
    /**
     *
     * @param string $path Relative or absolute path
     * @return string Absolute path
     */
    public function getAbsolutePath(string $path): string;
}
