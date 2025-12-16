<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Console;

use Symfony\Component\Console\Input\InputInterface;

final class CheckUnusedSeoUrlsCommand extends AbstractCheckSeoUrlsCommand
{
    protected static $defaultName = 'oe:consistency_check:check-unused-seo-urls';

    private const COMMAND_DESCRIPTION = 'Check for unused SEO URLs';
    private const MESSAGE_NO_RESULTS = 'No unused SEO URLs found';
    private const MESSAGE_RESULTS = 'Found %d unused SEO URLs';

    protected function findAllUrls(InputInterface $input): array
    {
        return $this->service->findUnusedUrls();
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
