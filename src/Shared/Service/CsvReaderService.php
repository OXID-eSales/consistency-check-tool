<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Service;

use League\Csv\UnableToProcessCsv;
use OxidEsales\ConsistencyCheck\Shared\Exception\CsvReadException;
use OxidEsales\ConsistencyCheck\Shared\Factory\CsvReaderFactoryInterface;
use OxidEsales\ConsistencyCheck\Shared\Mapper\CsvMapperInterface;

final class CsvReaderService implements CsvReaderServiceInterface
{
    public function __construct(
        private readonly PathResolverInterface $pathResolver,
        private readonly CsvReaderFactoryInterface $readerFactory,
    ) {
    }

    public function readFromCsv(string $filepath, CsvMapperInterface $mapper): array
    {
        $absolutePath = $this->pathResolver->getAbsolutePath($filepath);

        try {
            $csv = $this->readerFactory->create($absolutePath);
            $csv->setHeaderOffset(0);

            $dtos = [];
            foreach ($csv as $record) {
                $dtos[] = $mapper->fromArray($record);
            }

            return $dtos;
        } catch (UnableToProcessCsv $e) {
            throw new CsvReadException($filepath, $e);
        }
    }
}
