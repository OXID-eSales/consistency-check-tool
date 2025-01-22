<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Console;

use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageCheckerServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageEntityFilterServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageManagerServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\MessageFormatterServiceInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteUnusedImagesCommand extends Command
{
    protected static $defaultName = 'oe:consistency_check:delete-unused-images';

    private const MESSAGE_PROCESSING = 'Processing unused images for entity: %s';
    private const MESSAGE_DELETED_IMAGES = 'Deleted %d images for entity %s';
    private const MESSAGE_NO_IMAGES = 'No unused images found for entity %s';
    private const MESSAGE_COMPLETION = 'Unused image deletion operation completed.';
    private const ERROR_PROCESSING = 'Error processing entity %s: %s';

    public function __construct(
        private readonly iterable $entities,
        private readonly ImageCheckerServiceInterface $imageCheckerService,
        private readonly ImageManagerServiceInterface $imageManagerService,
        private readonly ImageEntityFilterServiceInterface $entityFilterService,
        private readonly MessageFormatterServiceInterface $messageFormatter,
        private readonly ProgressBar $progressBar,
        private readonly PsrLoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Deletes unused images.')
			// phpcs:ignore Generic.Files.LineLength.TooLong
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Entity type to process (product, category, manufacturer)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Perform a dry run without actual file deletions');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getOption('type');
        $dryRun = $input->getOption('dry-run');

        $filteredEntities = $this->entityFilterService->filterEntitiesByName($this->entities, $type);
        $this->progressBar->start(count($filteredEntities));

        foreach ($filteredEntities as $entity) {
            try {
                $this->processEntity($entity, $output, $dryRun);
            } catch (\Exception $e) {
                $entityDetails = sprintf('[%s:%s]', $entity->getName(), $entity->getFieldName());
                $this->logger->error(sprintf(self::ERROR_PROCESSING, $entityDetails, $e->getMessage()));
				// phpcs:ignore Generic.Files.LineLength.TooLong
                $output->writeln($this->messageFormatter->formatError(self::ERROR_PROCESSING, $entityDetails, $e->getMessage()));
            }

            $this->progressBar->advance();
        }

        $this->progressBar->finish();
        $output->writeln($this->messageFormatter->formatInfo(self::MESSAGE_COMPLETION));
        $this->logger->info(self::MESSAGE_COMPLETION);

        return Command::SUCCESS;
    }

    private function processEntity(ImageEntityInterface $entity, OutputInterface $output, bool $dryRun): void
    {
        $unusedImages = $this->imageCheckerService->getUnusedImages($entity);
        $entityDetails = sprintf('[%s:%s]', $entity->getName(), $entity->getFieldName());

        if ($unusedImages->getAll()) {
            $output->writeln($this->messageFormatter->formatInfo(self::MESSAGE_PROCESSING, $entityDetails));
            $this->logger->info(sprintf(self::MESSAGE_PROCESSING, $entityDetails));

            $this->imageManagerService->deleteImages($unusedImages, $dryRun);

			// phpcs:ignore Generic.Files.LineLength.TooLong
            $output->writeln($this->messageFormatter->formatInfo(self::MESSAGE_DELETED_IMAGES, count($unusedImages->getAll()), $entityDetails));
            $this->logger->info(sprintf(self::MESSAGE_DELETED_IMAGES, count($unusedImages->getAll()), $entityDetails));
        } else {
            $output->writeln($this->messageFormatter->formatComment(self::MESSAGE_NO_IMAGES, $entityDetails));
            $this->logger->info(sprintf(self::MESSAGE_NO_IMAGES, $entityDetails));
        }
    }
}
