<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Service;

use OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure\SeoUrlRepositoryInterface;
use OxidEsales\Eshop\Core\Config;

final class SeoUrlService implements SeoUrlServiceInterface
{
    private const DEFAULT_SUFFIX = 'oxid';

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
        // The suffix decision was copied from SeoEncoder::getSuffix().
        // The default suffix is hardcoded there.
        $effectiveSuffix = $suffix
            ?? $this->config->getConfigParam('sSEOuprefix')
            ?? self::DEFAULT_SUFFIX;

        return $this->repository->findDuplicateUrls($effectiveSuffix);
    }

    public function deleteUrls(array $seoUrlDtos): int
    {
        return $this->repository->deleteUrls($seoUrlDtos);
    }
}
