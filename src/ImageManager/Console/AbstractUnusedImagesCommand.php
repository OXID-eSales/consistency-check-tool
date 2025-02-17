<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Console;

use Exception;
use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ProgressBarFactoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageCheckerServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageEntityFilterServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageManagerServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\MessageFormatterServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractUnusedImagesCommand extends Command
{
    protected const MESSAGE_PROCESSING = 'Processing unused images for entity: %s';
    protected const MESSAGE_NO_IMAGES = 'No unused images found for entity %s';
    protected const MESSAGE_ERROR = 'Error processing entity %s: %s';
    protected const MESSAGE_ENTITY_EXCEPTION = 'Error processing entity %s: Check error log for details';

    public function __construct(
        /** @var ImageEntityInterface[] */
        protected readonly iterable $entities,
        protected readonly ImageCheckerServiceInterface $imageCheckerService,
        protected readonly ImageManagerServiceInterface $imageManagerService,
        protected readonly ImageEntityFilterServiceInterface $entityFilterService,
        protected readonly MessageFormatterServiceInterface $messageFormatter,
        protected readonly ProgressBarFactoryInterface $progressBarFactory,
        protected readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getOption('type');
        $filteredEntities = $this->entityFilterService->filterEntitiesByName($this->entities, $type);
        $progressBar = $this->progressBarFactory->create($output, count($filteredEntities));
        $progressBar->start();

        foreach ($filteredEntities as $entity) {
            try {
                $this->processEntity($entity, $input, $output);
            } catch (Exception $exception) {
                $this->reportError($entity, $exception, $output);
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->reportSuccess($output);

        return Command::SUCCESS;
    }

    private function processEntity(ImageEntityInterface $entity, InputInterface $input, OutputInterface $output): void
    {
        $unusedImages = $this->imageCheckerService->getUnusedImages($entity);

        if ($unusedImages->getAll()) {
            $this->reportProcessing($entity, $output);
            $affectedImagesCount = $this->processImages($unusedImages, $input);
            if ($affectedImagesCount > 0) {
                $this->reportProcessed($entity, $affectedImagesCount, $output);
            } else {
                $this->reportEntityException($entity, $output);
            }
        } else {
            $this->reportNoImages($entity, $output);
        }
    }

    abstract protected function processImages(ImageCollectionInterface $unusedImages, InputInterface $input): int;

    abstract protected function getMessageProcessedImages(): string;
    abstract protected function getMessageCompletion(): string;

    private function reportProcessing(ImageEntityInterface $entity, OutputInterface $output): void
    {
        $entityDetails = $this->getEntityDetailsString($entity);

        $output->writeln("\n" . $this->messageFormatter->formatInfo(self::MESSAGE_PROCESSING, $entityDetails));
        $this->logger->info(sprintf(self::MESSAGE_PROCESSING, $entityDetails));
    }

    private function reportProcessed(
        ImageEntityInterface $entity,
        int $affectedImagesCount,
        OutputInterface $output
    ): void {
        $message = $this->getMessageProcessedImages();
        $messageArgs = [$affectedImagesCount, $this->getEntityDetailsString($entity)];

        $output->writeln("\n" . $this->messageFormatter->formatInfo($message, ...$messageArgs));
        $this->logger->info(sprintf($message, ...$messageArgs));
    }

    private function reportNoImages(ImageEntityInterface $entity, OutputInterface $output): void
    {
        $entityDetails = $this->getEntityDetailsString($entity);

        $output->writeln("\n" . $this->messageFormatter->formatComment(self::MESSAGE_NO_IMAGES, $entityDetails));
        $this->logger->info(sprintf(self::MESSAGE_NO_IMAGES, $entityDetails));
    }

    private function reportError(ImageEntityInterface $entity, Exception $exception, OutputInterface $output): void
    {
        $messageArgs = [$this->getEntityDetailsString($entity), $exception->getMessage()];

        $this->logger->error(sprintf(static::MESSAGE_ERROR, ...$messageArgs));
        $output->writeln($this->messageFormatter->formatError(static::MESSAGE_ERROR, ...$messageArgs));
    }

    private function reportSuccess(OutputInterface $output): void
    {
        $this->logger->info($this->getMessageCompletion());
        $output->writeln("\n" . $this->messageFormatter->formatInfo($this->getMessageCompletion()));
    }

    private function reportEntityException(ImageEntityInterface $entity, OutputInterface $output): void
    {
        $messageArgs = [$this->getEntityDetailsString($entity)];

        $this->logger->error(sprintf(static::MESSAGE_ENTITY_EXCEPTION, ...$messageArgs));
        $output->writeln("\n" . $this->messageFormatter->formatError(self::MESSAGE_ENTITY_EXCEPTION, ...$messageArgs));
    }

    private function getEntityDetailsString(ImageEntityInterface $entity): string
    {
        return sprintf('[%s:%s]', $entity->getName(), $entity->getFieldName());
    }
}
