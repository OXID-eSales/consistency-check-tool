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

    private const COMMAND_DESCRIPTION = 'Check for unused SEO URLs';
    private const MESSAGE_NO_RESULTS = 'No unused SEO URLs found';
    private const MESSAGE_RESULTS = 'Found %d unused SEO URLs';
    private const MESSAGE_PROCESSING_ENTITY = 'Processing entity type: %s';
    private const MESSAGE_SKIPPING_ENTITY = 'Skipping entity %s - no reference table';
    private const MESSAGE_ENTITY_RESULTS = 'Found %d unused URLs for entity %s';

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
                $this->logger->debug(sprintf(self::MESSAGE_SKIPPING_ENTITY, $entity->getSeoType()));
                continue;
            }

            $this->logger->debug(sprintf(self::MESSAGE_PROCESSING_ENTITY, $entity->getSeoType()));
            $results = $this->service->findUnusedUrls($entity);
            $this->logger->info(sprintf(self::MESSAGE_ENTITY_RESULTS, count($results), $entity->getSeoType()));
            $allResults = array_merge($allResults, $results);
        }

        return $allResults;
    }

    protected function getCommandDescription(): string
    {
        return self::COMMAND_DESCRIPTION;
    }

    protected function getNoResultsMessage(): string
    {
        return $this->messageFormatter->formatInfo(self::MESSAGE_NO_RESULTS);
    }

    protected function getResultsMessage(int $count): string
    {
        return $this->messageFormatter->formatComment(self::MESSAGE_RESULTS, $count);
    }
}
