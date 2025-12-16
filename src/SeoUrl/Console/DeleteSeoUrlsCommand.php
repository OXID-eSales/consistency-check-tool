<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Console;

use OxidEsales\ConsistencyCheck\Export\Factory\ExportReaderConfigurationFactoryInterface;
use OxidEsales\ConsistencyCheck\Export\Service\ExportReaderServiceInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Service\SeoUrlServiceInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Service\SeoUrlTableRendererInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\MessageFormatterServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class DeleteSeoUrlsCommand extends Command
{
    protected static $defaultName = 'oe:consistency_check:delete-seo-urls';

    private const COMMAND_DESCRIPTION = 'Delete SEO URLs from CSV file (batch operation)';
    private const COMMAND_OPTION_FILE = 'Path to CSV file containing URLs to delete';
    private const COMMAND_OPTION_DRY_RUN = 'Perform a dry run without actual deletions';

    private const MESSAGE_FILE_REQUIRED = '--file option is required';
    private const MESSAGE_READING_CSV = 'Reading CSV file: %s';
    private const MESSAGE_NO_URLS_IN_CSV = 'No SEO URLs found in CSV file';
    private const MESSAGE_DRY_RUN = 'Dry run: Would delete %d SEO URLs (no actual deletion performed)';
    private const MESSAGE_PROCESSING = 'Processing %d SEO URLs...';
    private const MESSAGE_DELETING = 'Deleting %d SEO URLs';
    private const MESSAGE_DELETE_SUCCESS = 'Deleted %d SEO URLs successfully';

    public function __construct(
        private readonly SeoUrlServiceInterface $service,
        private readonly ExportReaderServiceInterface $csvReaderService,
        private readonly ExportReaderConfigurationFactoryInterface $readerConfigurationFactory,
        private readonly SeoUrlTableRendererInterface $tableRenderer,
        private readonly MessageFormatterServiceInterface $messageFormatter,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::COMMAND_DESCRIPTION)
            ->addOption('file', null, InputOption::VALUE_REQUIRED, self::COMMAND_OPTION_FILE)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, self::COMMAND_OPTION_DRY_RUN);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getOption('file');

        if (!$file) {
            $output->writeln($this->messageFormatter->formatError(self::MESSAGE_FILE_REQUIRED));
            return Command::FAILURE;
        }

        $this->logger->info(sprintf(self::MESSAGE_READING_CSV, $file));
        $output->writeln($this->messageFormatter->formatInfo(self::MESSAGE_READING_CSV, $file));

        $configuration = $this->readerConfigurationFactory->create($file);

        $dtos = $this->csvReaderService->read($configuration);

        if (empty($dtos)) {
            $this->logger->info(self::MESSAGE_NO_URLS_IN_CSV);
            $output->writeln($this->messageFormatter->formatInfo(self::MESSAGE_NO_URLS_IN_CSV));
            return Command::SUCCESS;
        }

        /** @var array<SeoUrlDtoInterface> $dtos */
        $this->tableRenderer->render($dtos, $output);

        $isDryRun = $input->getOption('dry-run');

        if ($isDryRun) {
            $this->logger->info(sprintf(self::MESSAGE_DRY_RUN, count($dtos)));
            $output->writeln($this->messageFormatter->formatComment(self::MESSAGE_DRY_RUN, count($dtos)));
            return Command::SUCCESS;
        }

        $output->writeln($this->messageFormatter->formatInfo(self::MESSAGE_PROCESSING, count($dtos)));

        $oxids = array_map(fn($dto) => $dto->getObjectId(), $dtos);

        $this->logger->warning(sprintf(self::MESSAGE_DELETING, count($oxids)));
        $deletedCount = $this->service->deleteUrls($oxids);
        $this->logger->info(sprintf(self::MESSAGE_DELETE_SUCCESS, $deletedCount));

        $output->writeln($this->messageFormatter->formatInfo(self::MESSAGE_DELETE_SUCCESS, $deletedCount));

        return Command::SUCCESS;
    }
}
