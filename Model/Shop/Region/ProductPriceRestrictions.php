<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Shop\Region;

class ProductPriceRestrictions
{
    private float $minProductPrice;
    private int $maxProductPrice;

    public function __construct(float $minProductPrice, int $maxProductPrice)
    {
        $this->minProductPrice = $minProductPrice;
        $this->maxProductPrice = $maxProductPrice;
    }

    public function getMinProductPrice(): float
    {
        return $this->minProductPrice;
    }

    public function getMaxProductPrice(): int
    {
        return $this->maxProductPrice;
    }
}
