<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\PackageDimension;

class Weight
{
    private string $value;
    private string $unit;

    public function __construct(string $value, string $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }
}
