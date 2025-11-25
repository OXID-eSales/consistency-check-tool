<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Service;

use League\Csv\Exception as CsvException;
use OxidEsales\ConsistencyCheck\Shared\Exception\InvalidFileFormatException;
use OxidEsales\ConsistencyCheck\Shared\Factory\CsvWriterFactoryInterface;
use OxidEsales\ConsistencyCheck\Shared\Mapper\CsvMapperInterface;

final class CsvExportService implements CsvExportServiceInterface
{
    public function __construct(
        private readonly PathResolverInterface $pathResolver,
        private readonly CsvWriterFactoryInterface $writerFactory,
    ) {
    }

    public function exportToCsv(
        array $data,
        string $filepath,
        CsvMapperInterface $mapper
    ): int {
        $absolutePath = $this->pathResolver->getAbsolutePath($filepath);

        try {
            $csv = $this->writerFactory->create($absolutePath);

            $headers = $mapper->getHeaders();
            $data = array_map(fn($dto) => $mapper->toArray($dto), $data);

            $csv->insertOne($headers);
            $csv->insertAll($data);
        } catch (CsvException $e) {
            throw new InvalidFileFormatException(
                "Cannot write CSV file: {$filepath}. Error: {$e->getMessage()}",
                0,
                $e
            );
        }

        return count($data);
    }
}
