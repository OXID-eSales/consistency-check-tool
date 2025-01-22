<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Console;

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

class MoveUnusedImagesCommand extends Command
{
    protected static $defaultName = 'oe:consistency_check:move-unused-images';

    private const MESSAGE_PROCESSING = 'Processing unused images for entity: %s';
    private const MESSAGE_MOVED_IMAGES = 'Moved %d images for entity %s';
    private const MESSAGE_NO_IMAGES = 'No unused images found for entity %s';
    private const MESSAGE_COMPLETION = 'Unused image move operation completed.';
    private const ERROR_PROCESSING = 'Error processing entity %s: %s';
    private const ERROR_DESTINATION_REQUIRED = 'Error: The --destination option is required.';

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
            ->setDescription('Moves unused images to a new destination.')
			// phpcs:ignore Generic.Files.LineLength.TooLong
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Entity type to process (product, category, manufacturer)')
            ->addOption('destination', null, InputOption::VALUE_REQUIRED, 'Destination path (required)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulate the move operation without making changes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $destination = $this->validateDestination($input, $output);
        if (!$destination) {
            return Command::INVALID;
        }

        $type = $input->getOption('type');
        $filteredEntities = $this->entityFilterService->filterEntitiesByName($this->entities, $type);
        $this->progressBar->start(count($filteredEntities));

        foreach ($filteredEntities as $entity) {
            try {
                $this->processEntity($entity, $input, $output);
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

    private function processEntity($entity, InputInterface $input, OutputInterface $output): void
    {
        $unusedImages = $this->imageCheckerService->getUnusedImages($entity);
        $entityDetails = sprintf('[%s:%s]', $entity->getName(), $entity->getFieldName());

        if ($unusedImages->getAll()) {
            $output->writeln($this->messageFormatter->formatInfo(self::MESSAGE_PROCESSING, $entityDetails));
            $this->logger->info(sprintf(self::MESSAGE_PROCESSING, $entityDetails));

			// phpcs:ignore Generic.Files.LineLength.TooLong
            $this->imageManagerService->moveImages($unusedImages, $input->getOption('destination'), $input->getOption('dry-run'));

			// phpcs:ignore Generic.Files.LineLength.TooLong
            $output->writeln($this->messageFormatter->formatInfo(self::MESSAGE_MOVED_IMAGES, count($unusedImages->getAll()), $entityDetails));
            $this->logger->info(sprintf(self::MESSAGE_MOVED_IMAGES, count($unusedImages->getAll()), $entityDetails));
        } else {
            $output->writeln($this->messageFormatter->formatComment(self::MESSAGE_NO_IMAGES, $entityDetails));
            $this->logger->info(sprintf(self::MESSAGE_NO_IMAGES, $entityDetails));
        }
    }

    private function validateDestination(InputInterface $input, OutputInterface $output): ?string
    {
        $destination = $input->getOption('destination');

        if (!$destination) {
            $output->writeln($this->messageFormatter->formatError(self::ERROR_DESTINATION_REQUIRED));
            return null;
        }

        return rtrim($destination, '/');
    }
}
