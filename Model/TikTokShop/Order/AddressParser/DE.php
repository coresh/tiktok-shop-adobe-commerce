<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order\AddressParser;

class DE extends \M2E\TikTokShop\Model\TikTokShop\Order\BaseAddressParser
{
    public function getCity(): string
    {
        foreach ($this->getDistricts() as $district) {
            if ($district['level'] === 'municipality') {
                return $district['name'];
            }
        }

        return '';
    }

    public function getState(): string
    {
        foreach ($this->getDistricts() as $district) {
            if ($district['level'] === 'federal state') {
                return $district['name'];
            }
        }

        return '';
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
        foreach ($this->getDistricts() as $district) {
            if ($district['level'] === 'urban district') {
                return $district['name'];
            }
        }

        return '';
    }
}
