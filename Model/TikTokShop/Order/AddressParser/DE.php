<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order\AddressParser;

class DE extends \M2E\TikTokShop\Model\TikTokShop\Order\BaseAddressParser
{
    public function getCity(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('municipality') ?? '';
    }

    public function getState(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('federal state') ?? '';
    }

    public function getStreetLines(): array
    {
        $streetLines = parent::getStreetLines();
        $streetLines[1] = !empty($streetLines[1])
            ? $streetLines[1] .  ', ' . $this->getUrbanDistrict()
            : $this->getUrbanDistrict();

        return $streetLines;
    }

    private function getUrbanDistrict(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('urban district') ?? '';
    }
}
