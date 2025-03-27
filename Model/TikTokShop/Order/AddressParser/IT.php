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
        foreach ($this->getDistricts() as $district) {
            if ($district['level'] === 'province') {
                return $district['name'];
            }
        }

        return '';
    }

    public function getCity(): string
    {
        foreach ($this->getDistricts() as $district) {
            if ($district['level'] === 'city') {
                return $district['name'];
            }
        }

        return $this->getMunicipality();
    }

    private function getMunicipality(): string
    {
        foreach ($this->getDistricts() as $district) {
            if ($district['level'] === 'municipality') {
                return $district['name'];
            }
        }

        return '';
    }

    private function getRegion(): string
    {
        foreach ($this->getDistricts() as $district) {
            if ($district['level'] === 'region') {
                return $district['name'];
            }
        }

        return '';
    }
}
