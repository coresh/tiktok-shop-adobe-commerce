<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions;

class WeightLimit
{
    private int $maxWeight;
    private int $minWeight;
    private string $unit;

    public function __construct(
        int $maxWeight,
        int $minWeight,
        string $unit
    ) {
        $this->maxWeight = $maxWeight;
        $this->minWeight = $minWeight;
        $this->unit = $unit;
    }

    public function getMaxWeight(): int
    {
        return $this->maxWeight;
    }

    public function getMinWeight(): int
    {
        return $this->minWeight;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }
}
