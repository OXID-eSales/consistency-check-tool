<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Exception;

final class MissingSuffixException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'SEO URL suffix is not configured. Please set sSEOuprefix in shop config or provide suffix parameter.'
        );
    }
}
