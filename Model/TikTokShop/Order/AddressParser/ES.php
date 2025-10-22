<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order\AddressParser;

class ES extends \M2E\TikTokShop\Model\TikTokShop\Order\BaseAddressParser
{
    public function getCity(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('municipality') ?? '';
    }

    public function getState(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('province') ?? '';
    }

    public function getStreetLines(): array
    {
        $streetLines = parent::getStreetLines();
        $streetLines[1] = !empty($streetLines[1])
            ? $streetLines[1] .  ', ' . $this->getAutonomousCommunity()
            : $this->getAutonomousCommunity();

        return $streetLines;
    }

    private function getAutonomousCommunity(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('autonomous community') ?? '';
    }
}
