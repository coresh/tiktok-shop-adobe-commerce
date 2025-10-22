<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order\AddressParser;

class IT extends \M2E\TikTokShop\Model\TikTokShop\Order\BaseAddressParser
{
    public function getStreetLines(): array
    {
        $streetLines = parent::getStreetLines();
        $streetLines[1] = !empty($streetLines[1])
            ? $streetLines[1] .  ', ' . $this->getRegion()
            : $this->getRegion();

        return $streetLines;
    }

    public function getState(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('province') ?? '';
    }

    public function getCity(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('city')
            ?? $this->getDistrictsCollection()->tryFindLevelName('municipality')
            ?? '';
    }

    private function getRegion(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('region') ?? '';
    }
}
