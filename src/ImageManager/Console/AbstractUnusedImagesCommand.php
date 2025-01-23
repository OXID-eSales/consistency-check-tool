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
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractUnusedImagesCommand extends Command
{
    protected const MESSAGE_PROCESSING = 'Processing unused images for entity: %s';
    protected const MESSAGE_NO_IMAGES = 'No unused images found for entity %s';
    protected const ERROR_PROCESSING = 'Error processing entity %s: %s';
    public function __construct(
        protected readonly iterable $entities,
        protected readonly ImageCheckerServiceInterface $imageCheckerService,
        protected readonly ImageManagerServiceInterface $imageManagerService,
        protected readonly ImageEntityFilterServiceInterface $entityFilterService,
        protected readonly MessageFormatterServiceInterface $messageFormatter,
        protected readonly ProgressBar $progressBar,
        protected readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getOption('type');
        $filteredEntities = $this->entityFilterService->filterEntitiesByName($this->entities, $type);
        $this->progressBar->start(count($filteredEntities));

        foreach ($filteredEntities as $entity) {
            try {
                $this->processEntity($entity, $input, $output);
            } catch (\Exception $e) {
                $entityDetails = sprintf('[%s:%s]', $entity->getName(), $entity->getFieldName());
                $this->logger->error(sprintf(static::ERROR_PROCESSING, $entityDetails, $e->getMessage()));
				// phpcs:ignore Generic.Files.LineLength.TooLong
                $output->writeln($this->messageFormatter->formatError(static::ERROR_PROCESSING, $entityDetails, $e->getMessage()));
            }
            $this->progressBar->advance();
        }

        $this->progressBar->finish();
        $output->writeln($this->messageFormatter->formatInfo(static::MESSAGE_COMPLETION));
        $this->logger->info(static::MESSAGE_COMPLETION);

        return Command::SUCCESS;
    }

    abstract protected function processEntity($entity, InputInterface $input, OutputInterface $output): void;
}
