<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product;

class PackageDimensionFinder
{
    private \M2E\TikTokShop\Model\Product\PackageDimension\SizeFinder $sizeFinder;
    private \M2E\TikTokShop\Model\Product\PackageDimension\WeightFinder $weightFinder;

    public function __construct(
        \M2E\TikTokShop\Model\Product\PackageDimension\SizeFinder $sizeFinder,
        \M2E\TikTokShop\Model\Product\PackageDimension\WeightFinder $weightFinder
    ) {
        $this->sizeFinder = $sizeFinder;
        $this->weightFinder = $weightFinder;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\PackageDimensionException
     */
    public function getWeight(\M2E\TikTokShop\Model\Product $product): PackageDimension\Weight
    {
        return $this->weightFinder->find($product);
    }

    /**
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\PackageDimensionException
     */
    public function getSize(\M2E\TikTokShop\Model\Product $product): PackageDimension\Size
    {
        return $this->sizeFinder->find($product);
    }
}
