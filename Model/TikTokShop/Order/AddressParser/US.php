<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order\AddressParser;

class US extends \M2E\TikTokShop\Model\TikTokShop\Order\AbstractAddressParser
{
    public function getCity(): string
    {
        foreach ($this->getDistricts() as $district) {
            if ($district['level'] === 'City') {
                return $district['name'];
            }
        }

        return '';
    }

    public function getState(): string
    {
        foreach ($this->getDistricts() as $district) {
            if ($district['level'] === 'State') {
                return $district['name'];
            }
        }

        return '';
    }
}
