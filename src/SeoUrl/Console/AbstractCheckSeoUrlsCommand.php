<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Console;

use OxidEsales\ConsistencyCheck\Export\Factory\ExportConfigurationFactoryInterface;
use OxidEsales\ConsistencyCheck\Export\Service\ExportServiceInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Service\SeoUrlServiceInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Service\SeoUrlTableRendererInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\MessageFormatterServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCheckSeoUrlsCommand extends Command
{
    private const COMMAND_OPTION_EXPORT = 'Export results to CSV file';

    protected const MESSAGE_RESULTS_FOUND = 'Found %d SEO URLs';
    protected const MESSAGE_EXPORT_START = 'Starting export of %d URLs to %s';
    protected const MESSAGE_EXPORT_SUCCESS = 'Exported %d URLs to %s';

    public function __construct(
        protected readonly SeoUrlServiceInterface $service,
        private readonly ExportServiceInterface $exportService,
        private readonly ExportConfigurationFactoryInterface $configurationFactory,
        private readonly SeoUrlTableRendererInterface $tableRenderer,
        protected readonly MessageFormatterServiceInterface $messageFormatter,
        protected readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription($this->getCommandDescription())
            ->addOption('export', null, InputOption::VALUE_NONE, self::COMMAND_OPTION_EXPORT);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $allResults = $this->findAllUrls($input);

        if (empty($allResults)) {
            $output->writeln($this->getNoResultsMessage());
            return Command::SUCCESS;
        }

        $this->logger->info(sprintf(static::MESSAGE_RESULTS_FOUND, count($allResults)));

        $this->tableRenderer->render($allResults, $output);

        if ($input->getOption('export')) {
            $configuration = $this->configurationFactory->create($allResults);
            $filePrefix = $configuration->getFilePrefix();
            $this->logger->info(sprintf(static::MESSAGE_EXPORT_START, count($allResults), $filePrefix));
            $exportFile = $this->exportService->export($configuration);
            $this->logger->info(sprintf(static::MESSAGE_EXPORT_SUCCESS, count($allResults), $exportFile));
            $output->writeln(
                $this->messageFormatter->formatInfo(static::MESSAGE_EXPORT_SUCCESS, count($allResults), $exportFile)
            );
        }

        $output->writeln($this->getResultsMessage(count($allResults)));

        return Command::SUCCESS;
    }

    /**
     * @return array<SeoUrlDtoInterface>
     */
    abstract protected function findAllUrls(InputInterface $input): array;

    abstract protected function getCommandDescription(): string;

    abstract protected function getNoResultsMessage(): string;

    abstract protected function getResultsMessage(int $count): string;
}
