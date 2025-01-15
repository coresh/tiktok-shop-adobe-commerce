<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Shop\Region;

class PackageWeightRestrictions
{
    private int $maxPackageWeight;
    private string $weightUnit;

    public function __construct(int $maxPackageWeight, string $weightUnit)
    {
        $this->maxPackageWeight = $maxPackageWeight;
        $this->weightUnit = $weightUnit;
    }

    public function getMaxPackageWeight(): int
    {
        return $this->maxPackageWeight;
    }

    public function getWeightUnit(): string
    {
        return $this->weightUnit;
    }
}
