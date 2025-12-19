<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Service;

use OxidEsales\ConsistencyCheck\SeoUrl\Exception\MissingSuffixException;
use OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure\SeoUrlRepositoryInterface;
use OxidEsales\Eshop\Core\Config;

final class SeoUrlService implements SeoUrlServiceInterface
{
    public function __construct(
        private readonly SeoUrlRepositoryInterface $repository,
        private readonly Config $config,
    ) {
    }

    public function findUnusedUrls(): array
    {
        return $this->repository->findUnusedUrls();
    }

    public function findDuplicateUrls(?string $suffix = null): array
    {
        $effectiveSuffix = $suffix ?? $this->config->getConfigParam('sSEOuprefix');

        if (empty($effectiveSuffix)) {
            throw new MissingSuffixException();
        }

        return $this->repository->findDuplicateUrls($effectiveSuffix);
    }

    public function deleteUrls(array $oxids): int
    {
        return $this->repository->deleteUrls($oxids);
    }
}
