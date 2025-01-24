<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Factory;

use OxidEsales\ConsistencyCheck\ImageManager\Factory\ProgressBarFactory;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ProgressBarFactoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;

class ProgressBarFactoryTest extends TestCase
{
    #[Test]
    public function itCreatesProgressBarSuccessfully(): void
    {
        $sut = $this->getSut();
        $output = new NullOutput();
        $maxSteps = rand(1, 100);

        $progressBar = $sut->create($output, $maxSteps);

        $this->assertInstanceOf(ProgressBar::class, $progressBar);
        $this->assertSame($maxSteps, $progressBar->getMaxSteps());
    }

    #[Test]
    public function itCanAdvanceProgressBar(): void
    {
        $sut = $this->getSut();
        $output = new NullOutput();
        $progressBar = $sut->create($output, 10);

        $progressBar->advance();
        $this->assertSame(1, $progressBar->getProgress());

        $progressBar->advance(3);
        $this->assertSame(4, $progressBar->getProgress());
    }

    #[Test]
    public function itFinishesProgressBar(): void
    {
        $sut = $this->getSut();
        $output = new NullOutput();
        $progressBar = $sut->create($output, $maxStep = rand(1, 100));

        $progressBar->finish();

        $this->assertSame($maxStep, $progressBar->getProgress());
    }
    private function getSut(): ProgressBarFactoryInterface
    {
        return new ProgressBarFactory();
    }
}
