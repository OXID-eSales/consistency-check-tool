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

    public function __construct(
        private readonly SeoUrlServiceInterface $service,
        private readonly ExportReaderServiceInterface $csvReaderService,
        private readonly ExportReaderConfigurationFactoryInterface $readerConfigurationFactory,
        private readonly SeoUrlTableRendererInterface $tableRenderer,
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
            $output->writeln('<error>--file option is required</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>Reading CSV file: %s</info>', $file));

        $configuration = $this->readerConfigurationFactory->create($file);

        $dtos = $this->csvReaderService->read($configuration);

        if (empty($dtos)) {
            $output->writeln('<info>No SEO URLs found in CSV file</info>');
            return Command::SUCCESS;
        }

        // Display table with all URLs
        /** @var array<SeoUrlDtoInterface> $dtos */
        $this->tableRenderer->render($dtos, $output);

        $isDryRun = $input->getOption('dry-run');

        if ($isDryRun) {
            $output->writeln(sprintf(
                '<comment>Dry run: Would delete %d SEO URLs (no actual deletion performed)</comment>',
                count($dtos)
            ));
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('<info>Processing %d SEO URLs...</info>', count($dtos)));

        // Extract OXOBJECTID from DTOs
        $oxids = array_map(fn($dto) => $dto->getObjectId(), $dtos);

        $deletedCount = $this->service->deleteUrls($oxids);

        $output->writeln(sprintf('<info>Deleted %d SEO URLs successfully</info>', $deletedCount));

        return Command::SUCCESS;
    }
}
