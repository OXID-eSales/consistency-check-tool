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

    //todo-critical: Has to be refactored. At first the name doesn't fit because it's handling two situations.
    //  Returning the absolute path and returning the absolute source path for a relative path.
    //  The places where this method is used should be checked as well, if it's possible/needed to pass an
    //  absolute(or relative path anyway).
    //todo-critical: After refactoring, we need to ensure that the FileLogReader and MonologConfigurationFactory can
    //  still handle absolute and relative paths like before.
    public function getAbsolutePath(string $path): string
    {
        if (Path::isAbsolute($path)) {
            return $path;
        }

        return Path::join($this->context->getSourcePath(), $path);
    }
}
