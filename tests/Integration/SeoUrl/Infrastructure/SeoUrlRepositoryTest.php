<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Integration\SeoUrl\Infrastructure;

use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDto;
use OxidEsales\ConsistencyCheck\SeoUrl\Factory\SeoUrlDtoFactoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure\SeoTypeTableMapping;
use OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure\SeoUrlRepository;
use OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure\SeoUrlRepositoryInterface;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Application\Model\Object2Category;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\Facts\Facts;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class SeoUrlRepositoryTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->get(QueryBuilderFactoryInterface::class)->create()->delete('oxseo')->execute();
    }

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
    public function findProductUrlsWithoutContext(): void
    {
        $contextId = uniqid();
        $productId = $this->insertArticleWithContext('category', $contextId);
        $seoUrl = uniqid() . '-without-context.html';
        $this->insertSeoUrl(
            objectId: $productId,
            seoUrl: $seoUrl,
            type: 'oxarticle',
            stdUrl: 'index.php?cl=details&amp;anid=' . $productId,
            params: $contextId,
            fixed: true
        );

        $result = $this->getSut()->findProductUrlsWithoutContext();

        $this->assertCount(1, $result);
        $this->assertSame($seoUrl, $result[0]->getSeoUrl());
    }

    public static function assignedContextDataProvider(): array
    {
        return [
            'category' => ['category'],
            'price category' => ['priceCategory'],
            'vendor' => ['vendor'],
            'manufacturer' => ['manufacturer'],
        ];
    }

    #[Test]
    #[DataProvider('assignedContextDataProvider')]
    public function findProductUrlsWithoutContextFindsAllAssignedContexts(string $contextType): void
    {
        $contextId = uniqid();
        $productId = $this->insertArticleWithContext($contextType, $contextId);
        $this->insertSeoUrl(
            objectId: $productId,
            seoUrl: uniqid() . '.html',
            type: 'oxarticle',
            stdUrl: 'index.php?cl=details&amp;anid=' . $productId,
            params: $contextId,
            fixed: true
        );

        $result = $this->getSut()->findProductUrlsWithoutContext();

        $this->assertCount(1, $result);
    }

    #[Test]
    public function findProductUrlsWithoutContextIgnoresNotFixedUrls(): void
    {
        $contextId = uniqid();
        $productId = $this->insertArticleWithContext('category', $contextId);
        $this->insertSeoUrl(
            objectId: $productId,
            seoUrl: uniqid() . '.html',
            type: 'oxarticle',
            stdUrl: 'index.php?cl=details&amp;anid=' . $productId,
            params: $contextId,
            fixed: false
        );

        $result = $this->getSut()->findProductUrlsWithoutContext();

        $this->assertEmpty($result);
    }

    #[Test]
    public function findProductUrlsWithoutContextIgnoresContextsNoLongerAssigned(): void
    {
        $productId = $this->insertArticleWithContext('category', uniqid());
        $this->insertSeoUrl(
            objectId: $productId,
            seoUrl: uniqid() . '.html',
            type: 'oxarticle',
            stdUrl: 'index.php?cl=details&amp;anid=' . $productId,
            params: uniqid(),
            fixed: true
        );

        $result = $this->getSut()->findProductUrlsWithoutContext();

        $this->assertEmpty($result);
    }

    #[Test]
    public function findProductUrlsWithoutContextCoversAllShops(): void
    {
        $vendorId = uniqid();
        $productId = uniqid();
        $this->insertArticle($productId, vendorId: $vendorId);
        $expectedShopIds = [1];

        if ($this->isNotCommunityEdition()) {
            $this->insertArticle($productId, shopId: 2, vendorId: $vendorId);
            $expectedShopIds[] = 2;
        }

        foreach ($expectedShopIds as $shopId) {
            $this->insertSeoUrl(
                objectId: $productId,
                seoUrl: uniqid() . '-shop' . $shopId . '.html',
                type: 'oxarticle',
                shopId: $shopId,
                stdUrl: 'index.php?cl=details&amp;anid=' . $productId,
                params: $vendorId,
                fixed: true
            );
        }

        $result = $this->getSut()->findProductUrlsWithoutContext();

        $shopIds = array_map(fn($dto) => $dto->getShopId(), $result);
        sort($shopIds);
        $this->assertSame($expectedShopIds, $shopIds);
    }

    public static function urlsWithValidContextDataProvider(): array
    {
        $contextId = 'test-context-id';

        return [
            'main url without context' => ['oxarticle', '', ''],
            'category' => ['oxarticle', '&amp;cnid=' . $contextId, $contextId],
            'vendor' => ['oxarticle', '&amp;cnid=v_' . $contextId . '&amp;listtype=vendor', $contextId],
            'manufacturer' => ['oxarticle', '&amp;mnid=' . $contextId . '&amp;listtype=manufacturer', $contextId],
            'recommendation list' => [
                'oxarticle',
                '&amp;recommid=' . $contextId . '&amp;listtype=recommlist',
                $contextId,
            ],
            'other seo type' => ['oxcategory', '', $contextId],
        ];
    }

    #[Test]
    #[DataProvider('urlsWithValidContextDataProvider')]
    public function findProductUrlsWithoutContextIgnoresValidUrls(
        string $type,
        string $stdUrlContext,
        string $params
    ): void {
        $productId = $this->insertArticleWithContext('category', 'test-context-id');
        $this->insertSeoUrl(
            objectId: $productId,
            seoUrl: uniqid() . '.html',
            type: $type,
            stdUrl: 'index.php?cl=details&amp;anid=' . $productId . $stdUrlContext,
            params: $params,
            fixed: true
        );

        $result = $this->getSut()->findProductUrlsWithoutContext();

        $this->assertEmpty($result);
    }

    #[Test]
    public function deleteUrls(): void
    {
        $oxid1 = uniqid();
        $oxid2 = uniqid();
        $oxid3 = uniqid();
        $shopId = 1;
        $languageId = random_int(0, 1);

        $this->insertSeoUrl(
            objectId: $oxid1,
            seoUrl: uniqid() . '.html',
            type: 'oxarticle',
            shopId: $shopId,
            lang: $languageId
        );
        $this->insertSeoUrl(
            objectId: $oxid2,
            seoUrl: uniqid() . '.html',
            type: 'oxarticle',
            shopId: $shopId,
            lang: $languageId
        );
        $this->insertSeoUrl(
            objectId: $oxid3,
            seoUrl: uniqid() . '.html',
            type: 'oxarticle',
            shopId: $shopId,
            lang: $languageId
        );

        $seoUrlDtos = [
            $this->createDto($oxid1, $shopId, $languageId),
            $this->createDto($oxid2, $shopId, $languageId),
        ];

        $deletedCount = $this->getSut()->deleteUrls($seoUrlDtos);

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

    #[Test]
    public function deleteUrlsOnlyDeletesSpecificShopAndLanguage(): void
    {
        $objectId = uniqid();

        $this->insertSeoUrl(
            objectId: $objectId,
            seoUrl: uniqid() . '-shop1-lang0.html',
            type: 'oxarticle',
            shopId: 1,
            lang: 0
        );
        $this->insertSeoUrl(
            objectId: $objectId,
            seoUrl: uniqid() . '-shop1-lang1.html',
            type: 'oxarticle',
            shopId: 1,
            lang: 1
        );

        $seoUrlDtos = [
            $this->createDto($objectId, 1, 0),
        ];

        $deletedCount = $this->getSut()->deleteUrls($seoUrlDtos);

        $this->assertSame(1, $deletedCount);

        $remaining = $this->getSut()->findUnusedUrls();
        $this->assertCount(1, $remaining);
        $this->assertSame($objectId, $remaining[0]->getObjectId());
        $this->assertSame(1, $remaining[0]->getLanguageId());
    }

    private function insertSeoUrl(
        string $objectId,
        string $seoUrl,
        string $type,
        int $shopId = 1,
        int $lang = 0,
        ?string $stdUrl = null,
        string $params = '',
        bool $fixed = false
    ): void {
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
                'ident' => md5($seoUrl . $shopId . $lang),
                'shopId' => $shopId,
                'lang' => $lang,
                'stdUrl' => $stdUrl ?? 'index.php?cl=details&anid=' . $objectId,
                'seoUrl' => $seoUrl,
                'type' => $type,
                'fixed' => (int)$fixed,
                'expired' => 0,
                'params' => $params,
            ])
            ->execute();
    }

    private function createDto(string $objectId, int $shopId, int $languageId): SeoUrlDto
    {
        return new SeoUrlDto(
            objectId: $objectId,
            ident: md5($objectId . $shopId . $languageId),
            shopId: $shopId,
            languageId: $languageId,
            stdUrl: 'index.php?cl=details&anid=' . $objectId,
            seoUrl: $objectId . '.html',
            type: 'oxarticle',
            fixed: (bool)random_int(0, 1),
            expired: (bool)random_int(0, 1),
            params: '',
            timestamp: date('Y-m-d H:i:s'),
        );
    }

    private function insertArticle(
        string $articleId,
        int $shopId = 1,
        string $vendorId = '',
        string $manufacturerId = '',
        float $price = 0
    ): void {
        $article = oxNew(Article::class);
        $article->setId($articleId);
        $article->setSkipAssign(true);
        $article->assign([
            'oxparentid' => '',
            'oxartnum' => uniqid(),
            'oxtitle' => uniqid(),
            'oxactive' => 1,
            'oxvendorid' => $vendorId,
            'oxmanufacturerid' => $manufacturerId,
            'oxprice' => $price,
        ]);
        $article->save();

        if ($this->isNotCommunityEdition()) {
            $article->assignToShop($shopId);
        }
    }

    private function insertArticleWithContext(string $contextType, string $contextId): string
    {
        $productId = uniqid();

        match ($contextType) {
            'category' => $this->insertArticle($productId),
            'priceCategory' => $this->insertArticle($productId, price: 50),
            'vendor' => $this->insertArticle($productId, vendorId: $contextId),
            'manufacturer' => $this->insertArticle($productId, manufacturerId: $contextId),
        };

        if ($contextType === 'category') {
            $object2Category = oxNew(Object2Category::class);
            $object2Category->setProductId($productId);
            $object2Category->setCategoryId($contextId);
            $object2Category->save();
        }

        if ($contextType === 'priceCategory') {
            $category = oxNew(Category::class);
            $category->setId($contextId);
            $category->assign([
                'oxparentid' => 'oxrootid',
                'oxtitle' => uniqid(),
                'oxactive' => 1,
                'oxpricefrom' => 10,
                'oxpriceto' => 100,
            ]);
            $category->save();
        }

        return $productId;
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
