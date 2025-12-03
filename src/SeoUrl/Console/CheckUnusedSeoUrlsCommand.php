<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Console;

use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Entity\SeoEntityInterface;
use Symfony\Component\Console\Input\InputInterface;

final class CheckUnusedSeoUrlsCommand extends AbstractCheckSeoUrlsCommand
{
    protected static $defaultName = 'oe:consistency_check:check-unused-seo-urls';

    /**
     * Find all unused URLs by iterating entities (skip static/dynamic)
     *
     * @return array<SeoUrlDtoInterface>
     */
    protected function findAllUrls(InputInterface $input): array
    {
        $allResults = [];

        foreach ($this->seoEntities as $entity) {
            // Skip static/dynamic entities (null reference table)
            if ($entity->getReferenceTable() === null) {
                continue;
            }

            $results = $this->service->findUnusedUrls($entity);
            $allResults = array_merge($allResults, $results);
        }

        return $allResults;
    }

    protected function getCommandDescription(): string
    {
        return 'Check for unused SEO URLs';
    }

    protected function getNoResultsMessage(): string
    {
        return '<info>No unused SEO URLs found</info>';
    }

    protected function getResultsMessage(int $count): string
    {
        return sprintf('<comment>Found %d unused SEO URLs</comment>', $count);
    }
}
