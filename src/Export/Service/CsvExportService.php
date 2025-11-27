<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Service;

use League\Csv\UnableToProcessCsv;
use OxidEsales\ConsistencyCheck\Export\Configuration\ExportConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Exception\CsvExportException;
use OxidEsales\ConsistencyCheck\Export\Factory\CsvWriterFactoryInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;

final class CsvExportService implements ExportServiceInterface
{
    public function __construct(
        private readonly PathResolverInterface $pathResolver,
        private readonly CsvWriterFactoryInterface $writerFactory,
    ) {
    }

    public function export(ExportConfigurationInterface $configuration): void
    {
        $absolutePath = $this->pathResolver->getAbsolutePath($configuration->getFilePath());

        try {
            $csv = $this->writerFactory->create($absolutePath);

            $csv->insertOne($configuration->getHeaders());

            $arrayFactory = $configuration->getArrayFactory();
            foreach ($configuration->getItems() as $dto) {
                $csv->insertOne($arrayFactory->createFromDto($dto));
            }
        } catch (UnableToProcessCsv $e) {
            throw new CsvExportException($configuration->getFilePath(), $e);
        }
    }
}
