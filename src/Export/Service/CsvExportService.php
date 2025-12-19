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
    private const FILE_EXTENSION = 'csv';

    public function __construct(
        private readonly PathResolverInterface $pathResolver,
        private readonly CsvWriterFactoryInterface $writerFactory,
        private readonly ExportFileNameGeneratorInterface $fileNameGenerator,
    ) {
    }

    public function export(ExportConfigurationInterface $configuration): string
    {
        $filePath = $this->fileNameGenerator->generate($configuration->getFilePrefix(), self::FILE_EXTENSION);
        $absolutePath = $this->pathResolver->getAbsolutePath($filePath);

        try {
            $csv = $this->writerFactory->create($absolutePath);

            $csv->insertOne($configuration->getHeaders());

            $arrayFactory = $configuration->getArrayFactory();
            foreach ($configuration->getItems() as $dto) {
                $csv->insertOne($arrayFactory->createFromDto($dto));
            }
        } catch (UnableToProcessCsv $e) {
            throw new CsvExportException($filePath, $e);
        }

        return $absolutePath;
    }
}
