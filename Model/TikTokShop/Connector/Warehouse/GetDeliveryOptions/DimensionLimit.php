<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions;

class DimensionLimit
{
    private int $maxHeight;
    private int $maxLength;
    private int $maxWidth;
    private string $unit;

    public function __construct(
        int $maxHeight,
        int $maxLength,
        int $maxWidth,
        string $unit
    ) {
        $this->maxHeight = $maxHeight;
        $this->maxLength = $maxLength;
        $this->maxWidth = $maxWidth;
        $this->unit = $unit;
    }

    public function getMaxHeight(): int
    {
        return $this->maxHeight;
    }

    public function getMaxLength(): int
    {
        return $this->maxLength;
    }

    public function getMaxWidth(): int
    {
        return $this->maxWidth;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }
}
