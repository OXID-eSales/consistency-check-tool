<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Console;

use Symfony\Component\Console\Input\InputInterface;

final class CheckProductSeoUrlsWithoutContextCommand extends AbstractCheckSeoUrlsCommand
{
    protected static $defaultName = 'oe:consistency_check:check-product-seo-urls-without-context';

    private const COMMAND_DESCRIPTION = 'Check for fixed product SEO URLs whose standard URL is missing the '
        . 'category, vendor or manufacturer context';
    private const MESSAGE_NO_RESULTS = 'No fixed product SEO URLs without context found';
    private const MESSAGE_RESULTS = 'Found %d fixed product SEO URLs without context. '
        . 'Save the SEO settings of these products again in the admin area to fix them';

    protected function findAllUrls(InputInterface $input): array
    {
        return $this->service->findProductUrlsWithoutContext();
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
