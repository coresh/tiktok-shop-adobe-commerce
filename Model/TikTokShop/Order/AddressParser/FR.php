<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order\AddressParser;

class FR extends \M2E\TikTokShop\Model\TikTokShop\Order\BaseAddressParser
{
    public function getState(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('department') ?? '';
    }

    public function getCity(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('arrondissement') ?? '';
    }

    public function getStreetLines(): array
    {
        $streetLines = parent::getStreetLines();
        $streetLines[1] = !empty($streetLines[1])
            ? $streetLines[1] .  ', ' . $this->getStreetLine()
            : $this->getStreetLine();

        return $streetLines;
    }

    private function getStreetLine(): string
    {
        $result = [
            $this->getDistrictsCollection()->tryFindLevelName('region') ?? '',
            $this->getDistrictsCollection()->tryFindLevelName('commune') ?? '',
        ];

        return implode(', ', array_filter($result));
    }
}
