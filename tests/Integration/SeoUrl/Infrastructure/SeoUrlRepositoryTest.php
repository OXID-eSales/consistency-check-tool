<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Integration\SeoUrl\Infrastructure;

use OxidEsales\ConsistencyCheck\SeoUrl\Factory\SeoUrlDtoFactoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure\SeoTypeTableMapping;
use OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure\SeoUrlRepository;
use OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure\SeoUrlRepositoryInterface;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\Facts\Facts;
use PHPUnit\Framework\Attributes\Test;

final class SeoUrlRepositoryTest extends IntegrationTestCase
{
    #[Test]
    public function findUnusedUrls(): void
    {
        // Article 1: exists in Shop 1 only
        $articleIdShop1 = uniqid();
        $this->insertArticle($articleIdShop1, shopId: 1);

        // SEO URL 1: Shop 1, references non-existing article → ORPHAN
        $orphanSeoUrlShop1 = uniqid() . '-shop1-orphan.html';
        $nonExistingArticleId = uniqid();
        $this->insertSeoUrl(
            objectId: $nonExistingArticleId,
            seoUrl: $orphanSeoUrlShop1,
            type: 'oxarticle',
            shopId: 1
        );

        // SEO URL 2: Shop 1, references Article 1 (same shop) → NOT ORPHAN
        $this->insertSeoUrl(
            objectId: $articleIdShop1,
            seoUrl: uniqid() . '-shop1-valid.html',
            type: 'oxarticle',
            shopId: 1
        );

        $expectedOrphanCount = 1;
        $orphanSeoUrlShop2 = null;

        if ($this->isNotCommunityEdition()) {
            // SEO URL 3: Shop 2, references Article 1 (exists in Shop 1 only) → ORPHAN
            $orphanSeoUrlShop2 = uniqid() . '-shop2-orphan.html';
            $this->insertSeoUrl(
                objectId: $articleIdShop1,
                seoUrl: $orphanSeoUrlShop2,
                type: 'oxarticle',
                shopId: 2
            );
            $expectedOrphanCount++;
        }

        $result = $this->getSut()->findUnusedUrls();

        $seoUrls = array_map(fn($dto) => $dto->getSeoUrl(), $result);

        $this->assertCount($expectedOrphanCount, $result);
        $this->assertContains($orphanSeoUrlShop1, $seoUrls);

        if ($this->isNotCommunityEdition()) {
            $this->assertContains($orphanSeoUrlShop2, $seoUrls);
        }
    }

    #[Test]
    public function findUnusedUrlsExcludesRootObjectId(): void
    {
        $this->insertSeoUrl(
            objectId: 'root',
            seoUrl: uniqid() . '.html',
            type: 'oxarticle'
        );

        $orphanedObjectId = uniqid();
        $this->insertSeoUrl(
            objectId: $orphanedObjectId,
            seoUrl: uniqid() . '.html',
            type: 'oxarticle'
        );

        $result = $this->getSut()->findUnusedUrls();

        $objectIds = array_map(fn($dto) => $dto->getObjectId(), $result);

        $this->assertNotContains('root', $objectIds);
        $this->assertContains($orphanedObjectId, $objectIds);
    }

    #[Test]
    public function findDuplicateUrls(): void
    {
        $suffix = uniqid();
        $urlWithSuffix = uniqid() . $suffix . '.html';

        $this->insertSeoUrl(
            objectId: uniqid(),
            seoUrl: $urlWithSuffix,
            type: 'oxarticle'
        );
        $this->insertSeoUrl(
            objectId: uniqid(),
            seoUrl: uniqid() . '.html',
            type: 'oxarticle'
        );

        $result = $this->getSut()->findDuplicateUrls($suffix);

        $this->assertCount(1, $result);
        $this->assertSame($urlWithSuffix, $result[0]->getSeoUrl());
    }

    #[Test]
    public function findDuplicateUrlsReturnsEmptyWhenNoMatches(): void
    {
        $this->insertSeoUrl(
            objectId: uniqid(),
            seoUrl: uniqid() . '.html',
            type: 'oxarticle'
        );

        $result = $this->getSut()->findDuplicateUrls(uniqid());

        $this->assertEmpty($result);
    }

    #[Test]
    public function findDuplicateUrlsReturnsEmptyWhenTableIsEmpty(): void
    {
        $result = $this->getSut()->findDuplicateUrls(uniqid());

        $this->assertEmpty($result);
    }

    #[Test]
    public function deleteUrls(): void
    {
        $oxid1 = uniqid();
        $oxid2 = uniqid();
        $oxid3 = uniqid();

        $this->insertSeoUrl(objectId: $oxid1, seoUrl: uniqid() . '.html', type: 'oxarticle');
        $this->insertSeoUrl(objectId: $oxid2, seoUrl: uniqid() . '.html', type: 'oxarticle');
        $this->insertSeoUrl(objectId: $oxid3, seoUrl: uniqid() . '.html', type: 'oxarticle');

        $deletedCount = $this->getSut()->deleteUrls([$oxid1, $oxid2]);

        $this->assertSame(2, $deletedCount);

        $remaining = $this->getSut()->findUnusedUrls();
        $this->assertCount(1, $remaining);
        $this->assertSame($oxid3, $remaining[0]->getObjectId());
    }

    #[Test]
    public function deleteUrlsWithEmptyArray(): void
    {
        $deletedCount = $this->getSut()->deleteUrls([]);

        $this->assertSame(0, $deletedCount);
    }

    private function insertSeoUrl(string $objectId, string $seoUrl, string $type, int $shopId = 1): void
    {
        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create();

        $queryBuilder
            ->insert('oxseo')
            ->values([
                'OXOBJECTID' => ':objectId',
                'OXIDENT' => ':ident',
                'OXSHOPID' => ':shopId',
                'OXLANG' => ':lang',
                'OXSTDURL' => ':stdUrl',
                'OXSEOURL' => ':seoUrl',
                'OXTYPE' => ':type',
                'OXFIXED' => ':fixed',
                'OXEXPIRED' => ':expired',
                'OXPARAMS' => ':params',
            ])
            ->setParameters([
                'objectId' => $objectId,
                'ident' => md5($seoUrl . $shopId),
                'shopId' => $shopId,
                'lang' => 0,
                'stdUrl' => 'index.php?cl=details&anid=' . $objectId,
                'seoUrl' => $seoUrl,
                'type' => $type,
                'fixed' => 0,
                'expired' => 0,
                'params' => '',
            ])
            ->execute();
    }

    private function insertArticle(string $articleId, int $shopId = 1): void
    {
        $article = oxNew(Article::class);
        $article->setId($articleId);
        $article->setSkipAssign(true);
        $article->assign([
            'oxparentid' => '',
            'oxartnum' => uniqid(),
            'oxtitle' => uniqid(),
            'oxactive' => 1
        ]);
        $article->save();

        if ($this->isNotCommunityEdition()) {
            $article->assignToShop($shopId);
        }
    }

    private function getSut(): SeoUrlRepositoryInterface
    {
        $mappings = [
            new SeoTypeTableMapping('oxarticle', 'oxarticles'),
        ];

        return new SeoUrlRepository(
            $this->get(QueryBuilderFactoryInterface::class),
            $this->get(SeoUrlDtoFactoryInterface::class),
            $mappings,
            $this->get(ContextInterface::class),
            $this->get(ShopAdapterInterface::class),
        );
    }

    private function isNotCommunityEdition(): bool
    {
        return (new Facts())->getEdition() !== 'CE';
    }
}
