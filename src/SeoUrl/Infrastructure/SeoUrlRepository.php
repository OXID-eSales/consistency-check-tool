<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure;

use Doctrine\DBAL\ForwardCompatibility\Result;
use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Factory\SeoUrlDtoFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;

/**
 * @phpstan-import-type SeoUrlTableRow from SeoUrlDtoFactoryInterface
 */
final class SeoUrlRepository implements SeoUrlRepositoryInterface
{
    private const ROOT_OBJECT_ID = 'root';
    private const DELETE_CHUNK_SIZE = 500;


    /**
     * @param iterable<SeoTypeTableMappingInterface> $seoTypeTableMappings
     */
    public function __construct(
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory,
        private readonly SeoUrlDtoFactoryInterface $seoUrlDtoFactory,
        private readonly iterable $seoTypeTableMappings,
        private readonly ContextInterface $context,
        private readonly ShopAdapterInterface $shopAdapter,
    ) {
    }

    public function findUnusedUrls(): array
    {
        $allDtos = [];
        foreach ($this->seoTypeTableMappings as $mapping) {
            $dtos = $this->findUnusedUrlsForMapping($mapping);
            $allDtos = array_merge($allDtos, $dtos);
        }
        return $allDtos;
    }

    /**
     * @return array<SeoUrlDtoInterface>
     */
    private function findUnusedUrlsForMapping(SeoTypeTableMappingInterface $mapping): array
    {
        $dtos = [];

        foreach ($this->context->getAllShopIds() as $shopId) {
            $seoViewName = $this->shopAdapter->generateDatabaseViewName('oxseo', 0, $shopId);
            $referenceViewName = $this->shopAdapter->generateDatabaseViewName(
                $mapping->getReferenceTable(),
                0,
                $shopId
            );

            $queryBuilder = $this->queryBuilderFactory->create();

            $queryBuilder
                ->select('s.*')
                ->from($seoViewName, 's')
                ->leftJoin('s', $referenceViewName, 'r', 's.OXOBJECTID = r.OXID')
                ->where('s.OXTYPE = :type')
                ->andWhere('s.OXSHOPID = :shopId')
                ->andWhere('r.OXID IS NULL')
                ->andWhere('s.OXOBJECTID != :rootId')
                ->setParameter('type', $mapping->getSeoType())
                ->setParameter('shopId', $shopId)
                ->setParameter('rootId', self::ROOT_OBJECT_ID);

            /** @var Result<array> $result */
            $result = $queryBuilder->execute();

            while ($row = $result->fetchAssociative()) {
                /** @var SeoUrlTableRow $row */
                $dtos[] = $this->seoUrlDtoFactory->createFromArray($row);
            }
        }

        return $dtos;
    }

    public function findDuplicateUrls(string $suffix): array
    {
        $queryBuilder = $this->queryBuilderFactory->create();

        $queryBuilder
            ->select('*')
            ->from('oxseo')
            ->where('OXSEOURL LIKE :pattern')
            ->setParameter('pattern', '%' . $suffix . '%');

        /** @var Result<array> $result */
        $result = $queryBuilder->execute();

        $dtos = [];
        while ($row = $result->fetchAssociative()) {
            /** @var SeoUrlTableRow $row */
            $dtos[] = $this->seoUrlDtoFactory->createFromArray($row);
        }

        return $dtos;
    }

    public function deleteUrls(array $seoUrlDtos): int
    {
        if (empty($seoUrlDtos)) {
            return 0;
        }

        $deleted = 0;
        $chunks = array_chunk($seoUrlDtos, self::DELETE_CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            $tuples = [];
            $params = [];

            foreach ($chunk as $dto) {
                $tuples[] = '(?, ?, ?)';
                $params[] = $dto->getObjectId();
                $params[] = $dto->getShopId();
                $params[] = $dto->getLanguageId();
            }

            $sql = sprintf(
                'DELETE FROM oxseo WHERE (OXOBJECTID, OXSHOPID, OXLANG) IN (%s)',
                implode(', ', $tuples)
            );

            $connection = $this->queryBuilderFactory->create()->getConnection();
            $result = $connection->executeStatement($sql, $params);
            $deleted += $result;
        }

        return $deleted;
    }
}
