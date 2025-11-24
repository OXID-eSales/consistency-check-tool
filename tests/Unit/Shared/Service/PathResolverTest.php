<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\Shared\Service;

use OxidEsales\ConsistencyCheck\Shared\Service\PathResolver;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PathResolverTest extends TestCase
{
    #[Test]
    public function getAbsolutePathReturnsAbsolutePathUnchanged(): void
    {
        $absolutePath = '/absolute/path/to/' . uniqid() . '.csv';

        $contextStub = $this->createStub(ContextInterface::class);
        $contextStub->method('getSourcePath')->willReturn(uniqid());

        $sut = $this->getSut($contextStub);
        $result = $sut->getAbsolutePath($absolutePath);

        $this->assertSame($absolutePath, $result);
    }

    #[Test]
    public function getAbsolutePathJoinsRelativePathWithSourcePath(): void
    {
        $sourcePath = '/var/www/' . uniqid();
        $relativePath = 'export/' . uniqid() . '.csv';
        $expectedPath = $sourcePath . '/' . $relativePath;

        $contextStub = $this->createStub(ContextInterface::class);
        $contextStub->method('getSourcePath')->willReturn($sourcePath);

        $sut = $this->getSut($contextStub);
        $result = $sut->getAbsolutePath($relativePath);

        $this->assertSame($expectedPath, $result);
    }

    #[Test]
    public function getAbsolutePathHandlesNestedRelativePath(): void
    {
        $sourcePath = '/var/www/' . uniqid();
        $dir1 = uniqid();
        $dir2 = uniqid();
        $filename = uniqid() . '.csv';
        $relativePath = "{$dir1}/{$dir2}/{$filename}";
        $expectedPath = "{$sourcePath}/{$dir1}/{$dir2}/{$filename}";

        $contextStub = $this->createStub(ContextInterface::class);
        $contextStub->method('getSourcePath')->willReturn($sourcePath);

        $sut = $this->getSut($contextStub);
        $result = $sut->getAbsolutePath($relativePath);

        $this->assertSame($expectedPath, $result);
    }

    private function getSut(?ContextInterface $context = null): PathResolverInterface
    {
        $context ??= $this->createStub(ContextInterface::class);
        return new PathResolver($context);
    }
}
