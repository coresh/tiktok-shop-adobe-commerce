<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order\AddressParser;

class ES extends \M2E\TikTokShop\Model\TikTokShop\Order\BaseAddressParser
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
            if ($district['level'] === 'province') {
                return $district['name'];
            }
        }

        return '';
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
        foreach ($this->getDistricts() as $district) {
            if ($district['level'] === 'autonomous community') {
                return $district['name'];
            }
        }

        return '';
    }
}
