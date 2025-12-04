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
use OxidEsales\ConsistencyCheck\SeoUrl\Entity\SeoEntityInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Exception\ExportDirectoryNotFoundException;
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
    protected const MESSAGE_EXPORT_DIR_NOT_FOUND = 'Export directory not found: %s';

    /**
     * @param iterable<SeoEntityInterface> $seoEntities
     */
    public function __construct(
        protected readonly SeoUrlServiceInterface $service,
        protected readonly iterable $seoEntities,
        private readonly ExportServiceInterface $exportService,
        private readonly ExportConfigurationFactoryInterface $configurationFactory,
        private readonly SeoUrlTableRendererInterface $tableRenderer,
        protected readonly MessageFormatterServiceInterface $messageFormatter,
        protected readonly LoggerInterface $logger,
        private readonly string $exportDirectoryPath,
        private readonly string $exportFilePrefix,
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

        // Display results in table
        $this->tableRenderer->render($allResults, $output);

        // Export if requested
        if ($input->getOption('export')) {
            $exportFile = $this->generateExportPath();
            $this->logger->info(sprintf(static::MESSAGE_EXPORT_START, count($allResults), $exportFile));
            $configuration = $this->configurationFactory->create($allResults, $exportFile);
            $this->exportService->export($configuration);
            $this->logger->info(sprintf(static::MESSAGE_EXPORT_SUCCESS, count($allResults), $exportFile));
            $output->writeln(
                $this->messageFormatter->formatInfo(static::MESSAGE_EXPORT_SUCCESS, count($allResults), $exportFile)
            );
        }

        $output->writeln($this->getResultsMessage(count($allResults)));

        return Command::SUCCESS;
    }

    /**
     * Find all URLs - subclasses must implement their own logic
     *
     * @return array<SeoUrlDtoInterface>
     */
    abstract protected function findAllUrls(InputInterface $input): array;

    abstract protected function getCommandDescription(): string;

    abstract protected function getNoResultsMessage(): string;

    abstract protected function getResultsMessage(int $count): string;

    private function generateExportPath(): string
    {
        if (!is_dir($this->exportDirectoryPath)) {
            $this->logger->error(sprintf(static::MESSAGE_EXPORT_DIR_NOT_FOUND, $this->exportDirectoryPath));
            throw new ExportDirectoryNotFoundException($this->exportDirectoryPath);
        }

        $timestamp = date('Y-m-d_H-i-s');
        return sprintf(
            '%s/%s-%s.csv',
            $this->exportDirectoryPath,
            $this->exportFilePrefix,
            $timestamp
        );
    }
}
