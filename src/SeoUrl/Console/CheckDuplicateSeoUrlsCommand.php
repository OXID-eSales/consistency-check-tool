<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Console;

use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class CheckDuplicateSeoUrlsCommand extends AbstractCheckSeoUrlsCommand
{
    protected static $defaultName = 'oe:consistency_check:check-duplicate-seo-urls';

    private const COMMAND_DESCRIPTION = 'Check for duplicate SEO URLs with collision suffixes';
    private const COMMAND_OPTION_SUFFIX = 'Custom suffix to search for (overrides shop config)';
    private const MESSAGE_NO_RESULTS = 'No duplicate SEO URLs found';
    private const MESSAGE_RESULTS = 'Found %d duplicate SEO URLs';

    protected function configure(): void
    {
        parent::configure();
        $this->addOption('suffix', null, InputOption::VALUE_REQUIRED, self::COMMAND_OPTION_SUFFIX);
    }

    /**
     * Find all duplicate URLs (global search, not entity-specific)
     *
     * @return array<SeoUrlDtoInterface>
     */
    protected function findAllUrls(InputInterface $input): array
    {
        $customSuffix = $input->getOption('suffix');
        return $this->service->findDuplicateUrls($customSuffix);
    }

    protected function getCommandDescription(): string
    {
        return self::COMMAND_DESCRIPTION;
    }

    protected function getNoResultsMessage(): string
    {
        return '<info>' . self::MESSAGE_NO_RESULTS . '</info>';
    }

    protected function getResultsMessage(int $count): string
    {
        return sprintf('<comment>' . self::MESSAGE_RESULTS . '</comment>', $count);
    }
}
