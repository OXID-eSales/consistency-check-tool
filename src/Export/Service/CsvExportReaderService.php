<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Service;

use League\Csv\UnableToProcessCsv;
use OxidEsales\ConsistencyCheck\Export\Configuration\ExportReaderConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Exception\CsvReadException;
use OxidEsales\ConsistencyCheck\Export\Factory\CsvReaderFactoryInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;

final class CsvExportReaderService implements ExportReaderServiceInterface
{
    public function __construct(
        private readonly PathResolverInterface $pathResolver,
        private readonly CsvReaderFactoryInterface $readerFactory,
    ) {
    }

    public function read(ExportReaderConfigurationInterface $configuration): array
    {
        $absolutePath = $this->pathResolver->getAbsolutePath($configuration->getFilePath());

        try {
            $csv = $this->readerFactory->create($absolutePath);
            $csv->setHeaderOffset(0);

            $dtos = [];
            $dtoFactory = $configuration->getDtoFactory();
            foreach ($csv as $record) {
                $dtos[] = $dtoFactory->createFromArray($record);
            }

            return $dtos;
        } catch (UnableToProcessCsv $e) {
            throw new CsvReadException($configuration->getFilePath(), $e);
        }
    }
}
