<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\PackageDimension;

class Size
{
    private string $length;
    private string $width;
    private string $height;
    private string $unit;

    public function __construct(string $length, string $width, string $height, string $unit)
    {
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->unit = $unit;
    }

    public function getLength(): string
    {
        return $this->length;
    }

    public function getWidth(): string
    {
        return $this->width;
    }

    public function getHeight(): string
    {
        return $this->height;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function getVolumeWeight(): string
    {
        return (string)round((float)$this->length * (float)$this->width * (float)$this->height, 4);
    }
}
