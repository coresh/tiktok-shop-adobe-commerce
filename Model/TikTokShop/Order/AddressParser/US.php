<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order\AddressParser;

class US extends \M2E\TikTokShop\Model\TikTokShop\Order\BaseAddressParser
{
    public function getCity(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('City') ?? '';
    }

    public function getState(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('State') ?? '';
    }

    public function getStreetLines(): array
    {
        $streetLines = parent::getStreetLines();
        $streetLines[1] = !empty($streetLines[1])
            ? $streetLines[1] .  ', ' . $this->getCounty()
            : $this->getCounty();

        return $streetLines;
    }

    public function getCounty(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('County') ?? '';
    }
}
