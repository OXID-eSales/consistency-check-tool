<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Console;

use OxidEsales\ConsistencyCheck\Export\Service\ExportServiceInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Exception\FilterNotFoundException;
use OxidEsales\ConsistencyCheck\ExportByFilter\Factory\ExportConfigurationFactoryInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Service\FilterRegistryInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\MessageFormatterServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ExportByFilterCommand extends Command
{
    protected static $defaultName = 'oe:consistency_check:export-by-filter';

    private const COMMAND_DESCRIPTION = 'Export data matching a filter to CSV';
    private const OPTION_FILTER_NAME = 'Filter name to use for export';
    private const MESSAGE_FILTER_REQUIRED = 'The --filter-name option is required';
    private const MESSAGE_EXPORTED = 'Exported %d records to %s';
    private const LOG_MESSAGE_EXPORTED = "Exported %d records with filter '%s' to %s";

    public function __construct(
        private readonly FilterRegistryInterface $filterRegistry,
        private readonly ExportConfigurationFactoryInterface $configurationFactory,
        private readonly ExportServiceInterface $exportService,
        private readonly MessageFormatterServiceInterface $messageFormatter,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::COMMAND_DESCRIPTION)
            ->addOption('filter-name', null, InputOption::VALUE_REQUIRED, self::OPTION_FILTER_NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filterName = $input->getOption('filter-name');

        if (!$filterName) {
            $output->writeln($this->messageFormatter->formatError(self::MESSAGE_FILTER_REQUIRED));
            return Command::FAILURE;
        }

        try {
            $filter = $this->filterRegistry->get($filterName);
        } catch (FilterNotFoundException $exception) {
            $output->writeln($this->messageFormatter->formatError($exception->getMessage()));
            return Command::FAILURE;
        }

        $configuration = $this->configurationFactory->createFromFilter($filter);

        $filePath = $this->exportService->export($configuration);

        $count = count($configuration->getItems());
        $output->writeln($this->messageFormatter->formatInfo(self::MESSAGE_EXPORTED, $count, $filePath));

        $this->logger->info(sprintf(self::LOG_MESSAGE_EXPORTED, $count, $filterName, $filePath));

        return Command::SUCCESS;
    }
}
