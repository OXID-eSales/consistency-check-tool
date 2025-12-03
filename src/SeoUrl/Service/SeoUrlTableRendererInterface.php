<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Service;

use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface SeoUrlTableRendererInterface
{
    /**
     * @param array<SeoUrlDtoInterface> $seoUrls
     */
    public function render(array $seoUrls, OutputInterface $output): void;
}
