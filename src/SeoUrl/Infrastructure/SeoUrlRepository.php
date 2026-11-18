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
    private const PRODUCT_SEO_TYPE = 'oxarticle';
    private const STD_URL_CONTEXT_PATTERNS = ['%cnid=%', '%mnid=%'];


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

    public function findProductUrlsWithoutContext(): array
    {
        $dtos = [];
        foreach ($this->context->getAllShopIds() as $shopId) {
            $dtos = array_merge($dtos, $this->findProductUrlsWithoutContextForShop($shopId));
        }

        return $dtos;
    }

    /**
     * Only fixed entries are relevant: entries that are not fixed are regenerated with the correct
     * standard URL by the shop itself (e.g. after "Update SEO URLs" in the admin area).
     * Entries whose context is no longer assigned to the product are skipped, because the shop does not
     * create links for that context anymore and the admin can not select it in the SEO tab.
     *
     * @return array<SeoUrlDtoInterface>
     */
    private function findProductUrlsWithoutContextForShop(int $shopId): array
    {
        $seoViewName = $this->shopAdapter->generateDatabaseViewName('oxseo', 0, $shopId);
        $articleViewName = $this->shopAdapter->generateDatabaseViewName('oxarticles', 0, $shopId);
        $object2CategoryViewName = $this->shopAdapter->generateDatabaseViewName('oxobject2category', 0, $shopId);
        $categoryViewName = $this->shopAdapter->generateDatabaseViewName('oxcategories', 0, $shopId);

        $queryBuilder = $this->queryBuilderFactory->create();

        $queryBuilder
            ->select('s.*')
            ->from($seoViewName, 's')
            ->innerJoin('s', $articleViewName, 'a', 's.OXOBJECTID = a.OXID')
            ->where('s.OXTYPE = :type')
            ->andWhere('s.OXSHOPID = :shopId')
            ->andWhere('s.OXFIXED = 1')
            ->andWhere("s.OXPARAMS != ''")
            ->setParameter('type', self::PRODUCT_SEO_TYPE)
            ->setParameter('shopId', $shopId);

        foreach (self::STD_URL_CONTEXT_PATTERNS as $index => $pattern) {
            $queryBuilder
                ->andWhere(sprintf('s.OXSTDURL NOT LIKE :contextPattern%d', $index))
                ->setParameter('contextPattern' . $index, $pattern);
        }

        $queryBuilder->andWhere($queryBuilder->expr()->or(
            's.OXPARAMS = a.OXVENDORID',
            's.OXPARAMS = a.OXMANUFACTURERID',
            sprintf(
                'EXISTS (SELECT 1 FROM %s o2c WHERE o2c.OXOBJECTID = a.OXID AND o2c.OXCATNID = s.OXPARAMS)',
                $object2CategoryViewName
            ),
            sprintf(
                'EXISTS (SELECT 1 FROM %s c WHERE c.OXID = s.OXPARAMS'
                . ' AND (c.OXPRICEFROM != 0 OR c.OXPRICETO != 0)'
                . ' AND (c.OXPRICEFROM = 0 OR c.OXPRICEFROM <= a.OXPRICE)'
                . ' AND (c.OXPRICETO = 0 OR c.OXPRICETO >= a.OXPRICE))',
                $categoryViewName
            ),
        ));

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
