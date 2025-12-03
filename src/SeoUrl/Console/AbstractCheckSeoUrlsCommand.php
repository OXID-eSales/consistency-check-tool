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
use OxidEsales\ConsistencyCheck\SeoUrl\Service\SeoUrlServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCheckSeoUrlsCommand extends Command
{
    private const COMMAND_OPTION_EXPORT = 'Export results to CSV file';

    /**
     * @param iterable<SeoEntityInterface> $seoEntities
     */
    public function __construct(
        protected readonly SeoUrlServiceInterface $service,
        protected readonly iterable $seoEntities,
        private readonly ExportServiceInterface $exportService,
        private readonly ExportConfigurationFactoryInterface $configurationFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription($this->getCommandDescription())
            ->addOption('export', null, InputOption::VALUE_REQUIRED, self::COMMAND_OPTION_EXPORT);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $allResults = $this->findAllUrls($input);

        if (empty($allResults)) {
            $output->writeln($this->getNoResultsMessage());
            return Command::SUCCESS;
        }

        // Display results in table
        $this->displayTable($allResults, $output);

        // Export if requested
        $exportFile = $input->getOption('export');
        if ($exportFile) {
            $configuration = $this->configurationFactory->create($allResults, $exportFile);
            $this->exportService->export($configuration);
            $output->writeln(sprintf('<info>Exported %d URLs to %s</info>', count($allResults), $exportFile));
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

    /**
     * @param array<SeoUrlDtoInterface> $results
     */
    private function displayTable(array $results, OutputInterface $output): void
    {
        $table = new Table($output);
        $table->setHeaders(['OXID', 'Type', 'SEO URL', 'Object ID']);

        foreach ($results as $dto) {
            $table->addRow([
                $dto->getIdent(),
                $dto->getType(),
                $dto->getSeoUrl(),
                $dto->getObjectId(),
            ]);
        }

        $table->render();
    }
}
