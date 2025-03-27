<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order\AddressParser;

class FR extends \M2E\TikTokShop\Model\TikTokShop\Order\BaseAddressParser
{
    public function getState(): string
    {
        foreach ($this->getDistricts() as $district) {
            if ($district['level'] === 'department') {
                return $district['name'];
            }
        }

        return '';
    }

    public function getCity(): string
    {
        foreach ($this->getDistricts() as $district) {
            if ($district['level'] === 'arrondissement') {
                return $district['name'];
            }
        }

        return '';
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
        $result = [];

        foreach ($this->getDistricts() as $district) {
            if ($district['level'] === 'region') {
                $result[] = $district['name'];
            } elseif ($district['level'] === 'commune') {
                $result[] = $district['name'];
            }
        }

        return implode(', ', $result);
    }
}
