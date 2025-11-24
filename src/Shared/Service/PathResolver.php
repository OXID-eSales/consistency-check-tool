<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Service;

use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use Symfony\Component\Filesystem\Path;

final class PathResolver implements PathResolverInterface
{
    public function __construct(
        private readonly ContextInterface $context,
    ) {
    }

    public function getAbsolutePath(string $path): string
    {
        if (Path::isAbsolute($path)) {
            return $path;
        }

        return Path::join($this->context->getSourcePath(), $path);
    }
}
